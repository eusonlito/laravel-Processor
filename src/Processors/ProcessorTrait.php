<?php
namespace Eusonlito\LaravelProcessor\Processors;

use Eusonlito\LaravelProcessor\Library;

use Exception;
use Input;
use Request;

use FormManager\Containers\Collection;
use FormManager\Containers\Group;

trait ProcessorTrait
{
    use Library\BotsTrait, Library\FilesTrait;

    protected function check($function, $form = null)
    {
        $post = Input::all();

        if (empty($post['_processor'])
        || (is_string($function) && ($post['_processor'] !== $function))
        || (is_array($function) && !in_array($post['_processor'], $function, true))) {
            return null;
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
            throw new Exception('<p>'.implode('</p><p>', self::formErrors($form)).'</p>');
        }

        $data = $form->val();

        unset($data['_processor'], $data['_token'], $data['created_at'], $data['updated_at']);

        return $data;
    }

    private static function formErrors($form)
    {
        $errors = [];

        foreach ($form as $input) {
            if ($input instanceof Collection) {
                $errors = array_merge($errors, self::FormErrors($input->template));
            } elseif ($input instanceof Group) {
                $errors = array_merge($errors, self::FormErrors($input));
            } elseif ($error = $input->error()) {
                $errors[] = (isset($input->label) ? $input->label : $input->attr('placeholder')).': '.$error;
            }
        }

        return array_map('strip_tags', $errors);
    }
}
