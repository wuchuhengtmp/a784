<?php

namespace App\Listeners;

use Dingo\Api\Event\ResponseWasMorphed;

class AddPaginationLinksToResponse
{
    public function handle(ResponseWasMorphed $event)
    {
        if ($event->response->getContent()['status_code'] !== 200 ) {
            $event->response->setStatusCode(200);
        }
    }
}
