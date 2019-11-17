<?php

$app->get('/', function ($request, $response, $args) {
    $this->logger->info('Hello!');
    return $response->withJSON([
        'name' => 'hello!',
        'sub' => $request->getAttribute('subject'),
    ]);
})->setName('showHome');

$app->get('/install', function($request, $response, $args) {
    $env = $this->settings['env'] ?? 'pro';
    $ctrolKey = $this->settings['installerKey'] ?? null;
    $queryKey = $request->getQueryParams()['key'] ?? null;
    if ($ctrolKey != $queryKey) {
        return $response->withJSON(['message' => 'Fail']);
    }
    $installer = new \App\Migration\Release000Migration($this->db);
    if ($installer->isInstalled() && $env == 'pro') {
        return $response->withJSON(['message' => 'Fail']);
    }
    $installer->down();
    $installer->up();
    $installer->populate();
    $installer->updateActions();
    return $response->withJSON(['message' => 'Success']);
});

$app->get('/update', function ($request, $response, $params) {
    $env = $this->settings['env'] ?? 'pro';
    $ctrolKey = $this->settings['installerKey'] ?? null;
    $queryKey = $request->getQueryParams()['key'] ?? null;
    if ($ctrolKey != $queryKey) {
        return $response->withJSON(['mensaje' => 'La actualización ha fallado']);
    }
    $mig1 = new \App\Migration\Release001Migration($this->db);
    if (!$mig1->isInstalled()) {
        $mig1->up();
        $mig1->populate();
        $mig1->updateActions();
    }
    $mig2 = new \App\Migration\Release002Migration($this->db);
    if (!$mig2->isInstalled()) {
        $mig2->up();
        $mig2->populate();
        $mig2->updateActions();
    }
    $mig3 = new \App\Migration\Release003Migration($this->db);
    if (!$mig3->isInstalled()) {
        $mig3->up();
        $mig3->populate();
        $mig3->updateActions();
    }
    return $response->withJSON(['mensaje' => 'Actualización exitosa']);
});

$app->get('/install-cities', function ($request, $response, $arg) {
    $ctrolKey = $this->settings['installerKey'] ?? null;
    $queryKey = $request->getQueryParams()['key'] ?? null;
    if ($ctrolKey != $queryKey) {
        return $response->withJSON(['message' => 'Fail']);
    }
    $loader = new \App\Util\DataLoader($this->db);
    if ($loader->dataAlreadyLoaded()) {
        return $response->withJSON(['message' => 'Fail']);
    }
    $loader->createRegions();
    $loader->createCountries();
    $loader->createRegisteredCities();
    // $loader->createCountrySpaces();
    return $response->withJSON(['message' => 'Success']);
});

$app->get('/test', function ($request, $response, $arg) {

    $json = '{"type":"Polygon","coordinates":[[[-61.368136813681,15.208056087092],[-61.476147614761,15.640648468807],[-61.260126012601,15.473193353304],[-61.368136813681,15.208056087092]]]}';
    $json = '{"type":"MultiPoint","coordinates":[[-61.368136813681,15.208056087092],[-61.476147614761,15.640648468807],[-61.260126012601,15.473193353304],[-61.368136813681,15.208056087092]]}';
    
    $polygon = new Grimzy\LaravelMysqlSpatial\Types\Polygon([new Grimzy\LaravelMysqlSpatial\Types\LineString([
        new Grimzy\LaravelMysqlSpatial\Types\Point(40.74894149554006, -73.98615270853043),
        new Grimzy\LaravelMysqlSpatial\Types\Point(40.74848633046773, -73.98648262023926),
        new Grimzy\LaravelMysqlSpatial\Types\Point(40.747925497790725, -73.9851602911949),
        new Grimzy\LaravelMysqlSpatial\Types\Point(40.74837050671544, -73.98482501506805),
        new Grimzy\LaravelMysqlSpatial\Types\Point(40.74894149554006, -73.98615270853043)
    ])]);
    
    $space = $this->db->create('App:Space');
    $space->geometry = $polygon;
    //$space->type = "Polygon";
    return $response->withJSON([
        'message' => $space->toArray(),
        'compare' => $polygon->toArray(),
    ]);
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
    $this->patch('/users/{usr}', 'userApiGate:updateUser')->setName('apiU1User');
    $this->get('/users/{usr}/groups', 'userApiGate:retrieveGroups')->setName('apiRNUserGroup');
    $this->put('/users/{usr}/password', 'userApiGate:updatePassword')->setName('apiU1UserPassword');

    $this->post('/users/{usr}/groups/{gro}', 'userApiGate:attachGroup')->setName('api1UserAtc1Group');
    $this->delete('/users/{usr}/groups/{gro}', 'userApiGate:detachGroup')->setName('api1UserDtc1Group');

    $this->post('/subjects/{sub}/roles/{rol}', 'userApiGate:attachRole')->setName('api1SubjectAtc1Role');
    $this->get('/subjects', 'userApiGate:retrieveSubjects')->setName('apiRNSubject');

    $this->get('/initiatives', 'initiativeApiGate:retrieveInitiatives')->setName('apiRNInitiative');
    $this->post('/initiatives', 'initiativeApiGate:createInitiative')->setName('apiC1Initiative');
    $this->get('/initiatives/{ini}', 'initiativeApiGate:retrieveInitiative')->setName('apiR1Initiative');
    $this->delete('/initiatives/{ini}', 'initiativeApiGate:deleteInitiative')->setName('apiD1Initiative');
    $this->patch('/initiatives/{ini}', 'initiativeApiGate:updateInitiative')->setName('apiU1Initiative');

    $this->post('/initiatives/{ini}/city', 'initiativeApiGate:attachCity')->setName('api1InitiativeAtc1City');
    $this->delete('/initiatives/{ini}/city', 'initiativeApiGate:detachCity')->setName('api1InitiativeDtc1City');
    $this->get('/initiatives/{ini}/members', 'initiativeApiGate:retrieveMembers')->setName('apiRNInitiativeSubject');
    $this->post('/initiatives/{ini}/members/{usr}', 'initiativeApiGate:addMember')->setName('api1InitiativeAdd1Member');
    $this->delete('/initiatives/{ini}/members/{usr}', 'initiativeApiGate:removeMember')->setName('api1InitiativeRmv1Member');
    $this->post('/initiatives/{ini}/followers/{usr}', 'initiativeApiGate:addFollower')->setName('api1InitiativeAdd1Follower');
    $this->delete('/initiatives/{ini}/followers/{usr}', 'initiativeApiGate:removeFollower')->setName('api1InitiativeRmv1Follower');

    $this->get('/terms', 'termApiGate:retrieveTerms')->setName('apiRNTerm');
    $this->post('/terms', 'termApiGate:createTerm')->setName('apiC1Term');
    $this->get('/terms/{trm}', 'termApiGate:retrieveTerm')->setName('apiR1Term');
    $this->delete('/terms/{trm}', 'termApiGate:deleteTerm')->setName('apiD1Term');

    $this->post('/initiatives/{ini}/terms', 'initiativeApiGate:attachTerms')->setName('api1InitiativeAtcNTerm');
    $this->delete('/initiatives/{ini}/terms/{trm}', 'initiativeApiGate:detachTerm')->setName('api1InitiativeDtc1Term');

    $this->post('/initiatives/{ini}/countries', 'initiativeApiGate:attachCountries')->setName('api1InitiativeAtcNCountry');
    $this->delete('/initiatives/{ini}/countries/{cou}', 'initiativeApiGate:detachCountry')->setName('api1InitiativeDtc1Country');

    $this->post('/users/{usr}/terms', 'userApiGate:attachTerms')->setName('api1UserAtcNTerm');
    $this->delete('/users/{usr}/terms/{trm}', 'userApiGate:detachTerm')->setName('api1UserDtc1Term');

    $this->get('/videos', 'videoApiGate:retrieveVideos')->setName('apiRNVideo');
    $this->post('/videos', 'videoApiGate:createVideo')->setName('apiC1Video');
    $this->get('/videos/{vid}', 'videoApiGate:retrieveVideo')->setName('apiR1Video');
    $this->delete('/videos/{vid}', 'videoApiGate:deleteVideo')->setName('apiD1Video');
    $this->patch('/videos/{vid}', 'videoApiGate:updateVideo')->setName('apiU1Video');

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
