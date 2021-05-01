<?php

namespace Sigil\Commands;

use Closure;
use Exedra\Routing\Group;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RouteListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'sigil:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered routes';

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * An array of all the registered routes.
     *
     * @var \Illuminate\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Method', 'URI', 'Name'];

    /**
     * Create a new route command instance.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setDescription('List all routes');
        $this->addArgument('property', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Route property(s)', array('name', 'action', 'method', 'tag', 'uri'));
        $this->addOption('name', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        /** @var \Exedra\Application $app */
//        $app = app(\Exedra\Application::class);

        $input = $this->input;

        $table = new Table($this->output);

        $header = $input->getArgument('property');

        $table->setHeaders($header);

        $previousRoute = null;

        $total = 0;

        /** @var Group $map */
        $map = app('root_group');

        $map->each(function (\Exedra\Routing\Route $route) use ($table, $header, $input, &$total) {
            $routeName = $route->getAbsoluteName();

            $methods = $route->getMethod();

            if (count($methods) == 4)
                $methods = 'any';
            else
                $methods = implode(', ', $methods);

            // list only routes that is executable
            if (!$route->hasExecution())
                return;

            if ($name = $input->getOption('name'))
                if (strpos($routeName, $name) === false)
                    return;

            $row = array();

            $action = null;

            if (is_string($execute = $route->getProperty('execute')) && strpos($execute, 'routeller=') === 0) {
                $action = str_replace('routeller=', '', $execute);
            } else {
                if (is_object($execute) && $execute instanceof \Closure) {
                    $ref = new \ReflectionFunction($execute);

//                    $rootDir = $app->getRootDir();

                    $action = ltrim(str_replace('', '', $ref->getFileName()), '\\/') . ' (' . $ref->getStartLine() . ')';
                } else {
                    $action = '(' . gettype($route->getProperty('execute')) . ')';
                }
            }

            $data = array(
                'name' => str_replace('apis.', '', $route->getAbsoluteName()),
                'action' => str_replace('App\\API\\Controllers\\', '', $action),
                'method' => count($route->getMethod()) == 6 ? 'any' : $methods,
                'uri' => '/' . $route->getPath(true),
                'tag' => $route->hasProperty('tag') ? $route->getProperty('tag') : ''
            );

            foreach ($header as $col) {
                $col = strtolower($col);

                $row[] = $data[$col];
            }

            $table->addRow($row);

            $total++;
        }, true);

        if ($total == 0)
            $table->addRow(array(new TableCell('<info>Can\'t find any route</info>', array(
                'colspan' => count($header)
            ))));

        $this->output->writeln('Showing list of routes (' . $total . ') : ');

        $table->render();
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = collect($this->routes)->map(function ($route) {
            return $this->getRouteInformation($route);
        })->all();

        if ($sort = $this->option('sort')) {
            $routes = $this->sortRoutes($sort, $routes);
        }

        if ($this->option('reverse')) {
            $routes = array_reverse($routes);
        }

        return array_filter($routes);
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return $this->filterRoute([
//            'host'   => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri'    => $route->uri(),
            'name'   => $route->getName(),
//            'action' => $route->getActionName(),
//            'middleware' => $this->getMiddleware($route),
        ]);
    }

    /**
     * Sort the routes by a given element.
     *
     * @param  string  $sort
     * @param  array  $routes
     * @return array
     */
    protected function sortRoutes($sort, $routes)
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * Display the route information on the console.
     *
     * @param  array  $routes
     * @return void
     */
    protected function displayRoutes(array $routes)
    {
        $this->table($this->headers, $routes);
    }

    /**
     * Get before filters.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->implode(',');
    }

    /**
     * Filter the route by URI and / or name.
     *
     * @param  array  $route
     * @return array|null
     */
    protected function filterRoute(array $route)
    {
        if (($this->option('name') && ! Str::contains($route['name'], $this->option('name'))) ||
            $this->option('path') && ! Str::contains($route['uri'], $this->option('path')) ||
            $this->option('method') && ! Str::contains($route['method'], strtoupper($this->option('method')))) {
            return;
        }

        return $route;
    }

//    /**
//     * Get the console command options.
//     *
//     * @return array
//     */
//    protected function getOptions()
//    {
//        return [
//            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method.'],
//
//            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name.'],
//
//            ['path', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by path.'],
//
//            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes.'],
//
//            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (host, method, uri, name, action, middleware) to sort by.', 'uri'],
//        ];
//    }
}
