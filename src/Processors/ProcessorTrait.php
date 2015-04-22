<?php

namespace Laravel\Processor\Processors;

use Laravel\Processor\Library;

use Exception;
use Input;
use Request;

trait Processor
{
    use Library\BotTrait, Library\FilesTrait;

    protected function check($function, $form = null)
    {
        $post = Input::all();

        if (empty($post['_processor']) || ($post['_processor'] !== $function)) {
            return;
        }

        if (self::isFake($post, $form)) {
            throw new Exception(__('Not allowed'));
        }

        unset($post['_token'], $post['created_at'], $post['updated_at']);

        if ($form === null) {
            return $post;
        }

        $form->loadFromGlobals();

        if ($form->isValid() !== true) {
            $errors = [];

            foreach ($form as $input) {
                if ($input->error()) {
                    $errors[] = ($input->label() ?: $input->attr('placeholder')).': '.$input->error();
                }
            }

            throw new Exception('<p>'.implode('</p><p>', $errors).'</p>');
        }

        $data = $form->val();

        unset($data['_processor'], $data['_token'], $data['created_at'], $data['updated_at']);

        return $data;
    }
}
