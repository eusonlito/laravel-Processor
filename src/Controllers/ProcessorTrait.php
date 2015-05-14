<?php

namespace Laravel\Processor\Controllers;

use Exception;
use App;
use Input;
use Request;
use Response;
use Session;

trait ProcessorTrait
{
    protected function getProcessorClass()
    {
        return str_replace('\\Controllers', '\\Processors', get_class($this));
    }

    protected function processor($processor, $form = null, $params = null)
    {
        if (!($processor = $this->checkProcessor($processor))) {
            return;
        }

        return $this->makeProcessor($processor, $form, $params);
    }

    protected function checkProcessor($processor)
    {
        if (!Request::isMethod('post') || empty($_processor = Input::get('_processor'))) {
            return;
        }

        if (is_array($processor)) {
            return in_array($_processor, $processor, true) ? $_processor : null;
        }

        if ($processor === 'AUTO') {
            return $_processor;
        }

        return ($processor === $_processor) ? $_processor : null;
    }

    protected function makeProcessor($processor, $form = null, $params = null)
    {
        try {
            return App::make($this->getProcessorClass())->$processor($form, $params);
        } catch (Exception $e) {
            return $this->setProcessorMessage($e);
        }
    }

    protected function setProcessorMessage($e)
    {
        $message = $e->getMessage();

        if (config('app.debug')) {
            $message = '['.$e->getFile().' - '.$e->getLine().'] '.$message;
        }

        if (Request::ajax()) {
            return Response::make($message, (($e->getCode() === 404) ? 404 : 500));
        }

        Session::flash('flash-message', [
            'message' => $message,
            'status' => 'danger',
        ]);

        return false;
    }
}
