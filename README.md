# laravel-Processor

Split POST action into independent processes.

## Installation

using composer

```
"require": {
    "eusonlito/laravel-processor": "0.*"
}
```

#### app/Http/Controllers/Controller.php

```php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Eusonlito\LaravelProcessor\Controllers\ProcessorTrait;

abstract class Controller extends BaseController
{
    use ProcessorTrait;
}
```

#### app/Http/Controllers/Users.php

```php
namespace App\Http\Controllers;

class Users extends Controller
{
    public function login()
    {
        // https://github.com/oscarotero/form-manager
        $form = new Forms\Users\Login();

        if (is_object($processor = $this->processor(__FUNCTION__, $form))) {
            return $processor;
        }

        // https://github.com/eusonlito/laravel-Meta
        // https://github.com/oscarotero/Gettext
        Meta::meta('title', __('Login as User'));

        return view('web.pages.users.login', [
            'form' => $form,
        ]);
    }
}
```

#### app/Http/Controllers/Forms/Users/Login.php

```php
namespace App\Http\Controllers\Forms\Users;

use FormManager\Builder as B;
use FormManager\Containers\Form as F;

class Login extends F
{
    public function __construct()
    {
        return $this->method('post')->add([
            '_processor' => B::hidden()->val('login'),

            'user' => B::text()->attr([
                'placeholder' => __('Your user'),
                'autofocus' => true,
                'required' => true
            ]),
            'password' => B::password()->attr([
                'placeholder' => __('Your passsword'),
                'required' => true
            ]),
            'remember' => B::checkbox()->attr([
                'placeholder' => __('Remember me'),
                'value' => '1'
            ])
        ]);
    }
}
```

#### app/Http/Processors/Processor.php

```php
namespace App\Http\Processors;

use Eusonlito\LaravelProcessor\Processors\ProcessorTrait;

abstract Processor
{
    use ProcessorTrait;
}
```

#### app/Http/Processors/Users.php

```php
namespace App\Http\Processors;

use Exception;
use Auth;
use Hash;
use Redirect;
use Request;
use App\Models;

class Users extends Processor
{
    public function login($form)
    {
        // Check if is a valid request to execute this function
        // Also, validate $form
        if (!($data = $this->check(__FUNCTION__, $form))) {
            return $data;
        }

        $success = Auth::attempt([
            'user' => $data['user'],
            'password' => $data['password'],
        ], $data['remember']);

        if ($success !== true) {
            Models\UserSession::create([
                'user' => $data['user'],
                'ip' => Request::getClientIp(),
                'success' => 0,
            ]);

            throw new Exception(__('Incorrect user or password'));
        }

        Models\UserSession::create([
            'user' => $data['user'],
            'ip' => Request::getClientIp(),
            'success' => 1,
            'users_id' => Auth::user()->id,
        ]);

        return Redirect::back();
    }
}
```

#### resources/view/web/pages/users/login.php

```php
@if ($flash = Session::get('flash-message')) ?>

{{ Session::forget('flash-message') }}

<div class="alert alert-{{ $flash['status'] }} fade in">
    <div class="container text-center">
        <a class="close" data-dismiss="alert" href="#">×</a>
        {!! $flash['message'] !!}
    </div>
</div>

@endif

<form method="post">
    <div class="regular-signup">
        <h3 class="text-center">{{ __('Login into your account') }}</h3>

        {!! $form->html() !!}

        <button type="submit" class="btn btn-primary btn-lg btn-block">
            {{ __('Login') }}
        </button>
    </div>
</form>
```