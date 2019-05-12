<?php
$container = $app->getContainer();

$container['db'] = function ($c) {
    $settings = $c->get('settings')['eloquent'];
    return new App\Service\EloquentService($settings);
};
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['monolog'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler(
        $settings['path'],
        Monolog\Logger::DEBUG)
    );
    return $logger;
};
$container['renderer'] = function ($c) {
    return new Slim\Views\PhpRenderer(__DIR__."/templates");
};
$container['mailer'] = function ($c) {
    $settings = $c->get('settings')['swiftmailer'];
    $transport = new App\Util\SwiftTransportManager();
    $mailer = new Illuminate\Mail\Mailer(
        new App\Util\ViewFactory($c->get('renderer')),
        new Swift_Mailer($transport->getDriver($settings['transport'], $settings['transportOptions'])),
        null
    );
    if (isset($settings['mailerOptions']['from'])) {
        $address = $settings['mailerOptions']['from'];
        $mailer->alwaysFrom($address['address'], $address['name']);
    }
    return $mailer;
};
$container['jwt'] = function ($c) {
    return new App\Service\JwtService($c->get('settings')['jwt']);
};
$container['validation'] = function ($c) {
    return new Augusthur\JsonRespector\ValidatorService();
};
$container['pagination'] = function ($c) {
    return new App\Service\PaginationService($c->get('validation'));
};
$container['session'] = function ($c) {
    return new App\Auth\SessionManager\JWTSessionManager($c->get('jwt'));
};
$container['identity'] = function ($c) {
    return new App\Auth\Service\IdentityService([
        'local' => new App\Auth\IdentityProvider\LocalIdentityProvider(
            $c->get('db'), $c->get('validation')
        ),
    ], $c->get('db'));
};
$container['authorization'] = function ($c) {
    return new App\Auth\Service\AuthorizationService($c['db'], $c['logger']);
};
$container['helper'] = function ($c) {
    return new App\Service\HelperService(
        $c->get('validation'), $c->get('router'), $c->get('request')
    );
};
$container['resources'] = function ($c) {
    return new App\Service\ResourcesService($c);
};
$container['sessionApiGate'] = function ($c) {
    return new App\Gate\SessionApiGate($c);
};
$container['userApiGate'] = function ($c) {
    return new App\Gate\UserApiGate($c);
};
$container['initiativeApiGate'] = function ($c) {
    return new App\Gate\InitiativeApiGate($c);
};
$container['cityApiGate'] = function ($c) {
    return new App\Gate\CityApiGate($c);
};
$container['errorHandler'] = function ($c) {
    return new App\Service\ErrorHandlerService(
        [
            '\Respect\Validation\Exceptions\NestedValidationException' => function($e) {
                return [
                    'message' => 'Datos ingresados inválidos',
                    'status' => 400,
                    'code' => 'invalidData',
                    'errors' => $e->getMessages(),
                ];
            },
            '\Illuminate\Database\Eloquent\ModelNotFoundException' => function($e) {
                $m = end(explode('\\', $e->getModel()));
                return [
                    'message' => $m . ' not found',
                    'status' => 404,
                    'code' => 'notFound',
                ];
            },
            '\App\Util\Exception\AppException' => function($e) {
                return [
                    'message' => $e->getMessage(),
                    'status' => $e->getCode(),
                    'code' => $e->getType(),
                ];
            },
            '\App\Util\Exception\SystemException' => function($e) {
                return [
                    'message' => 'Error crítico del sistema',
                    'status' => 500,
                    'code' => 'fatalError',
                ];
            },
        ],
        $c->get('settings')['env'],
        $c->get('logger')
    );
};
