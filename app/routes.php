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

$app->get('/install-cities', function ($req, $res, $arg) {
    $loader = new \App\Util\DataLoader($this->db);
    $loader->createRegions();
    $loader->createCountries();
    $loader->createRegisteredCities();
    return $res->withJSON([
        'sub' => $this->session->authenticate($req)->toArray()
    ]);
    //return $res->withJSON($this->session->get('user'));
});

// $app->post('/upload', function ($request, $response, $args) {
//     $id = bin2hex(random_bytes(16)); // generate a unique id

//     // optionally send the content type in the header
//     $contentType = $request->getHeader('Content-Type') ?? 'application/octet-stream';

//     // the request body is the content of the file
//     $data = $request->getBody()->getContents();
//     file_put_contents($id, $data);

//     // return some information on the file we just stored
//     return $response->withJson([
//         'id' => $id,
//         'content-type' => $contentType,
//         'content-length' => strlen($data)
//     ]);
// });

$app->group('/v1', function () {
    $this->post('/tokens', 'sessionApiGate:createSession');
    $this->post('/pending-users', 'userApiGate:createPendingUser')->setName('apiC1PendingUser')
        ->add(new \App\Middleware\RecaptchaMiddleware($this->getContainer()));
    $this->post('/reset-tokens', 'userApiGate:createResetToken')->setName('apiC1ResetToken')
        ->add(new \App\Middleware\RecaptchaMiddleware($this->getContainer()));
    $this->post('/users', 'userApiGate:createUser')->setName('apiC1User');
    $this->get('/users/{usr}', 'userApiGate:retrieveUser')->setName('apiR1User');
    $this->put('/users/{usr}/password', 'userApiGate:updateUserPassword')->setName('apiU1UserPassword');

    $this->get('/initiatives', 'initiativeApiGate:retrieveInitiatives')->setName('apiRNInitiative');
    $this->post('/initiatives', 'initiativeApiGate:createInitiative')->setName('apiC1Initiative');
    $this->get('/initiatives/{ini}', 'initiativeApiGate:retrieveInitiative')->setName('apiR1Initiative');

    $this->get('/terms', 'termApiGate:retrieveTerms')->setName('apiRNTerm');
    $this->post('/terms', 'termApiGate:createTerm')->setName('apiC1Term');
    $this->get('/terms/{trm}', 'termApiGate:retrieveTerm')->setName('apiR1Term');

    $this->post('/initiatives/{ini}/terms', 'initiativeApiGate:attachTerms')->setName('api1InitiativeAtcNTerm');
    $this->delete('/initiatives/{ini}/terms/{trm}', 'initiativeApiGate:detachTerm')->setName('api1InitiativeDtc1Term');

    $this->get('/regions', 'regionApiGate:retrieveRegions')->setName('apiRNRegion');
    $this->get('/regions/{reg}', 'regionApiGate:retrieveRegion')->setName('apiR1Region');
    $this->get('/countries', 'countryApiGate:retrieveCountries')->setName('apiRNCountry');
    $this->get('/countries/{cou}', 'countryApiGate:retrieveCountry')->setName('apiR1Country');
    $this->get('/cities', 'cityApiGate:retrieveCities')->setName('apiRNCity');
    $this->get('/cities/{cit}', 'cityApiGate:retrieveCity')->setName('apiR1City');

    $this->get('/registered-cities', 'cityApiGate:retrieveRegisteredCities')->setName('apiRNRegisteredCity');
    $this->post('/registered-cities', 'cityApiGate:createRegisteredCity')->setName('apiC1RegisteredCity');
});

//$app->get('/send-mail', 'App\ExampleController:sendMail');

//$app->get('/query-db', 'App\ExampleController:queryDB');
