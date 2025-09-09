<?php
namespace Stilmark\Base\Test\Controllers;

final class ListController
{
    public function staticVars(): array
    {
        return [
            'userStatus' => ['active', 'inactive', 'deleted']
        ];
    }
}