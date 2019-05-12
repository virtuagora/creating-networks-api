<?php

$app->get('/', function ($request, $response, $args) {
    $this->logger->info('Hello!');
    return $response->withJSON([
        'name' => 'hello!',
        'sub' => $request->getAttribute('subject'),
    ]);
})->setName('showHome');

$app->get('/install', function($request, $response, $params) {
    $env = $this->settings['env'] ?? 'pro';
    $installer = new \App\Migration\Release000Migration($this->db);
    if ($installer->isInstalled() && $env == 'pro') {
        return $response->withJSON(['mensaje' => 'La instalación ha fallado']);
    }
    $installer->down();
    $installer->up();
    $installer->populate();
    $installer->updateActions();
    return $response->withJSON(['message' => 'instalación exitosa']);
});

$app->get('/test', function ($req, $res, $arg) {
    $loader = new \App\Util\DataLoader($this->db);
    //$loader->createRegions();
    //$loader->createCountries();
    $loader->createRegisteredCities();
    return $res->withJSON([
        'sub' => $this->session->authenticate($req)->toArray()
    ]);
    //return $res->withJSON($this->session->get('user'));
});

$app->group('/v1', function () {
    $this->post('/tokens', 'sessionApiGate:createSession');
    $this->post('/pending-users', 'userApiGate:createPendingUser')->setName('apiC1PendingUser');
    $this->post('/users', 'userApiGate:createUser')->setName('apiC1User');
    $this->get('/users/{usr}', 'userApiGate:retrieveUser')->setName('apiR1User');

    $this->post('/initiatives', 'initiativeApiGate:createInitiative')->setName('apiC1Initiative');
    $this->get('/initiatives/{ini}', 'initiativeApiGate:retrieveInitiative')->setName('apiR1Initiative');

    $this->get('/regions', 'regionApiGate:retrieveRegions')->setName('apiRNRegion');
    $this->get('/regions/{reg}', 'regionApiGate:retrieveRegion')->setName('apiR1Region');
    $this->get('/countries', 'countryApiGate:retrieveCountries')->setName('apiRNCountry');
    $this->get('/countries/{cou}', 'countryApiGate:retrieveCountry')->setName('apiR1Country');
    $this->get('/cities', 'cityApiGate:retrieveCities')->setName('apiRNCity');
    $this->get('/cities/{cit}', 'cityApiGate:retrieveCity')->setName('apiR1City');

    $this->get('/registered-cities', 'cityApiGate:retrieveRegisteredCities')->setName('apiRNRegisteredCity');
});

//$app->get('/send-mail', 'App\ExampleController:sendMail');

//$app->get('/query-db', 'App\ExampleController:queryDB');
