<?php

namespace Mine\NextCoreX\Traits;

use Hyperf\Collection\Arr;
use Hyperf\WebSocketServer\Context;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server;
use Swow\Psr7\Server\ServerConnection;

trait WebsocketServerTrait
{
    use ClientTrait,LocalDataTrait;

    /**
     * @param Response|Server|ServerConnection $server
     * @param Request $request
     */
    protected function handleOpen($server, Request $request)
    {
        $clientId = $this->getClientContract()->generatorId();
        Context::set('client_id',$clientId);
        $clientIds = $this->getLocalData()->get('client_ids',[]);
        $clientIds[] = $clientId;
        $this->getLocalData()->set('client_ids',$clientIds);
    }

    protected function getClientId(): string
    {
        return Context::get('client_id');
    }

    /**
     * @param Response|\Swoole\Server $server
     */
    protected function handleClose($server,int $fd,int $reactorId)
    {
        $clientIds = $this->getLocalData()->get('client_ids',[]);
        $clientIds = Arr::where($clientIds,function ($clientId){
            return $this->getClientId() !== $clientId;
        });
        $this->getLocalData()->set('client_ids',$clientIds);
    }
}