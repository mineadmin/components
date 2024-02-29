<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Mine\Office\Excel;

use Mine\Exception\MineException;
use Mine\MineModel;
use Mine\MineRequest;
use Mine\MineResponse;
use Mine\Office\ExcelPropertyInterface;
use Mine\Office\MineExcel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Vtiful\Kernel\Excel;
use Vtiful\Kernel\Format;

use function Hyperf\Collection\data_get;

class XlsWriter extends MineExcel implements ExcelPropertyInterface
{
    public static function getSheetData(mixed $request)
    {
        $file = $request->file('file');
        $tempFileName = 'import_' . time() . '.' . $file->getExtension();
        $tempFilePath = BASE_PATH . '/runtime/' . $tempFileName;
        file_put_contents($tempFilePath, $file->getStream()->getContents());
        $xlsxObject = new Excel(['path' => BASE_PATH . '/runtime/']);
        return $xlsxObject->openFile($tempFileName)->openSheet()->getSheetData();
    }

    /**
     * 导入数据.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function import(MineModel $model, ?\Closure $closure = null): bool
    {
        $request = container()->get(MineRequest::class);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $tempFileName = 'import_' . time() . '.' . $file->getExtension();
            $tempFilePath = BASE_PATH . '/runtime/' . $tempFileName;
            file_put_contents($tempFilePath, $file->getStream()->getContents());
            $xlsxObject = new Excel(['path' => BASE_PATH . '/runtime/']);
            $data = $xlsxObject->openFile($tempFileName)->openSheet()->getSheetData();
            unset($data[0]);

            $importData = [];
            foreach ($data as $item) {
                $tmp = [];
                foreach ($item as $key => $value) {
                    $tmp[$this->property[$key]['name']] = (string) $value;
                }
                $importData[] = $tmp;
            }

            if ($closure instanceof \Closure) {
                return $closure($model, $importData);
            }

            try {
                foreach ($importData as $item) {
                    $model::create($item);
                }
                @unlink($tempFilePath);
            } catch (\Exception $e) {
                @unlink($tempFilePath);
                throw new \Exception($e->getMessage());
            }
            return true;
        }
        return false;
    }

    /**
     * 导出excel.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function export(string $filename, array|\Closure $closure, \Closure $callbackData = null): ResponseInterface
    {
        $filename .= '.xlsx';
        is_array($closure) ? $data = &$closure : $data = $closure();

        $aligns = [
            'left' => Format::FORMAT_ALIGN_LEFT,
            'center' => Format::FORMAT_ALIGN_CENTER,
            'right' => Format::FORMAT_ALIGN_RIGHT,
        ];

        $columnName = [];
        $columnField = [];

        foreach ($this->property as $item) {
            $columnName[] = $item['value'];
            $columnField[] = $item['name'];
        }

        $tempFileName = 'export_' . time() . '.xlsx';
        $xlsxObject = new Excel(['path' => BASE_PATH . '/runtime/']);
        $fileObject = $xlsxObject->fileName($tempFileName)->header($columnName);
        $columnFormat = new Format($fileObject->getHandle());
        $rowFormat = new Format($fileObject->getHandle());

        $i = 0;
        foreach ($this->property as $index => $item) {
            $fileObject->setColumn(
                sprintf('%s1:%s1', $this->getColumnIndex($i), $this->getColumnIndex($i)),
                $this->property[$index]['width'] ?? mb_strlen($columnName[$i]) * 5,
                $columnFormat->align($this->property[$index]['align'] ? $aligns[$this->property[$index]['align']] : $aligns['left'])
                    ->background($this->property[$index]['bgColor'] ?? Format::COLOR_WHITE)
                    ->border(Format::BORDER_THIN)
                    ->fontColor($this->property[$index]['color'] ?? Format::COLOR_BLACK)
                    ->toResource()
            );
            ++$i;
        }

        // 表头加样式
        $fileObject->setRow(
            sprintf('A1:%s1', $this->getColumnIndex(count($columnField))),
            20,
            $rowFormat->bold()->align(Format::FORMAT_ALIGN_CENTER, Format::FORMAT_ALIGN_VERTICAL_CENTER)
                ->background(0x4AC1FF)->fontColor(Format::COLOR_BLACK)
                ->border(Format::BORDER_THIN)
                ->toResource()
        );
        $exportData = [];
        foreach ($data as $item) {
            $yield = [];
            if ($callbackData) {
                $item = $callbackData($item);
            }
            foreach ($this->property as $property) {
                foreach ($item as $name => $value) {
                    if ($property['name'] == $name) {
                        if (! empty($property['dictName'])) {
                            $yield[] = $property['dictName'][$value];
                        } elseif (! empty($property['dictData'])) {
                            $yield[] = $property['dictData'][$value];
                        } elseif (! empty($property['path'])) {
                            $yield[] = data_get($item, $property['path']);
                        } elseif (! empty($this->dictData[$name])) {
                            $yield[] = $this->dictData[$name][$value] ?? '';
                        } else {
                            $yield[] = $value;
                        }
                        break;
                    }
                }
            }
            $exportData[] = $yield;
        }

        $response = container()->get(MineResponse::class);
        $filePath = $fileObject->data($exportData)->output();

        $response->download($filePath, $filename);

        ob_start();
        if (copy($filePath, 'php://output') === false) {
            throw new MineException('导出数据失败', 500);
        }
        $res = $this->downloadExcel($filename, ob_get_contents());
        ob_end_clean();

        @unlink($filePath);

        return $res;
    }
}
