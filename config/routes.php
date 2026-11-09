<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        $builder->connect('/', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/student', ['controller' => 'Dashboard', 'action' => 'student']);
        $builder->connect('/counselor', ['controller' => 'Counselor', 'action' => 'overview']);
        $builder->connect('/admin', ['controller' => 'Dashboard', 'action' => 'admin']);

        // Language switch (English / BM)
        $builder->connect('/lang/{code}', ['controller' => 'Language', 'action' => 'change'])
            ->setPass(['code'])
            ->setPatterns(['code' => 'en|ms']);

        // Student appointments
        $builder->connect('/student/book', ['controller' => 'Appointments', 'action' => 'book']);
        $builder->connect('/student/sessions', ['controller' => 'Appointments', 'action' => 'index']);
        $builder->connect('/student/sessions/{id}', ['controller' => 'Appointments', 'action' => 'view'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/student/sessions/{id}/cancel', ['controller' => 'Appointments', 'action' => 'cancel'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);

        $builder->connect('/student/sessions/{id}/accept', ['controller' => 'Appointments', 'action' => 'acceptProposal'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d+']);
        $builder->connect('/student/sessions/{id}/decline', ['controller' => 'Appointments', 'action' => 'declineProposal'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d+']);

        // Counselor appointments
        $builder->connect('/counselor/appointments', ['controller' => 'CounselorAppointments', 'action' => 'index']);
        $builder->connect('/counselor/appointments/{id}', ['controller' => 'CounselorAppointments', 'action' => 'view'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        foreach (['confirm', 'propose', 'decline', 'cancel'] as $action) {
            $builder->connect('/counselor/appointments/{id}/' . $action, ['controller' => 'CounselorAppointments', 'action' => $action])
                ->setPass(['id'])
                ->setPatterns(['id' => '\d+']);
        }

        // Notifications (students and counselors)
        $builder->connect('/notifications', ['controller' => 'Notifications', 'action' => 'index']);
        $builder->connect('/notifications/read-all', ['controller' => 'Notifications', 'action' => 'readAll']);
        $builder->connect('/notifications/{id}/open', ['controller' => 'Notifications', 'action' => 'open'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d+']);

        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        $builder->connect('/pages/*', 'Pages::display');

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * It is NOT recommended to use fallback routes after your initial prototyping phase!
         * See https://book.cakephp.org/5/en/development/routing.html#fallbacks-method for more information
         */
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};