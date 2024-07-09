<?php

namespace Plugin\MineAdmin\PayGateway\Controller;

use Hyperf\Collection\Arr;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Mine\Annotation\Auth;
use Mine\Annotation\Permission;
use Mine\MineController;
use Plugin\MineAdmin\PayGateway\Request\CrudRequest as Request;
use Plugin\MineAdmin\PayGateway\Service\CrudService;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: 'plugin/mineadmin/pay_gateway')]
#[Auth]
class CrudController extends MineController
{
    #[Inject]
    private CrudService $crudService;

    #[Inject]
    private FilesystemFactory $filesystemFactory;

    /**
     * 查询列表
     */
    #[GetMapping(path: 'index')]
    #[Permission('plugin:mineadmin:pay_gateway:index')]
    public function index(Request $request): ResponseInterface
    {
        return $this->success($this->crudService->page($request->validated()));
    }

    /**
     * 创建通道
     */
    #[PostMapping(path: 'create')]
    #[Permission('plugin:mineadmin:pay_gateway:create')]
    public function create(Request $request): ResponseInterface
    {
        return $this->crudService->create($request->validated())
            ? $this->success()
            : $this->error();
    }

    /**
     * 更新通道
     */
    #[PutMapping(path: 'save')]
    #[Permission('plugin:mineadmin:pay_gateway:save')]
    public function save(Request $request): ResponseInterface
    {
        return $this->crudService->save(
            (int)$request->input('id'),
            Arr::except($request->validated(),'id')
        )? $this->success() : $this->error();
    }

    /**
     * 删除，批量删除
     */
    #[DeleteMapping(path: 'delete')]
    #[Permission('plugin:mineadmin:pay_gateway:delete')]
    public function delete(Request $request): ResponseInterface
    {
        return $this->crudService->delete($request->input('ids'))
            ? $this->success()
            : $this->error();
    }

    /**
     * 获取全局配置项
     */
    #[GetMapping(path: 'get_global_setting')]
    #[Permission('plugin:mineadmin:pay_gateway:get_global_setting')]
    public function getGlobalSetting(): ResponseInterface
    {
        return $this->success(
            $this->crudService->getGlobalSetting()
        );
    }

    #[PostMapping(path: 'set_global_setting')]
    #[Permission('plugin:mineadmin:pay_gateway:set_global_setting')]
    public function setGlobalSetting(Request $request): ResponseInterface
    {
        return $this->success(
            $this->crudService->setGlobalSetting($request->validated())
        );
    }

    #[PostMapping(path: 'upload')]
    public function upload(Request $request): ResponseInterface
    {
        $filesystem = $this->filesystemFactory->get('local');
        $uploadFile = $request->file('file');
        @copy($uploadFile->getRealPath(),$uploadedFilePath = sys_get_temp_dir().'/'.uniqid('upload'));
        $filename = time().'.crt';
        $folder = 'plugin/mineadmin/pay_gateway';
        if (!$filesystem->directoryExists($folder)){
            $filesystem->createDirectory($folder);
        }
        $filesystem->write($folder.'/'.$filename,file_get_contents($uploadedFilePath));
        @unlink($uploadedFilePath);
        return $this->success([
            'path'  =>  $folder.'/'.$filename,
        ]);
    }
}