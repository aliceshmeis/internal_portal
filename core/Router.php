<?php
class Router {
    private $routes = [];

    public function add($url, $controller, $action = 'index') {
        $this->routes[$url] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($url) {
        $url = strtok($url, '?');
        $url = trim($url, '/');
        
        if (empty($url)) {
            $url = '';
        }

        if (isset($this->routes[$url])) {
            $controller = $this->routes[$url]['controller'];
            $action = $this->routes[$url]['action'];
        } else {
            $parts = explode('/', $url);
            $controller = ucfirst($parts[0]) . 'Controller';
            $action = isset($parts[1]) ? $parts[1] : 'index';
        }

        $controllerFile = APP_PATH . 'controllers/' . $controller . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerObj = new $controller();
            
            if (method_exists($controllerObj, $action)) {
                $controllerObj->$action();
            } else {
                $this->error404();
            }
        } else {
            $this->error404();
        }
    }

    private function error404() {
        http_response_code(404);
        require_once APP_PATH . 'views/errors/404.php';
        exit;
    }
}
?>