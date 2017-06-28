<?php

use Mellivora\Application\App;
use Mellivora\Support\Arr;
use Mellivora\Support\Collection;
use Mellivora\Support\HigherOrderTapProxy;
use Mellivora\Support\Str;
use Slim\Http\Uri;

if (!defined('__ROOT__')) {
    /**
     * 定义项目根目录路径
     */
    define('__ROOT__', realpath(__DIR__ . '/../../../'));
}

if (!function_exists('app')) {
    /**
     * 获取 app 容器，或者注入容器的实例
     *
     * @param  string  $id
     * @return mixed
     */
    function app($id = null)
    {
        $container = App::instance()->getContainer();

        return $id ? $container->get($id) : $container;
    }
}

if (!function_exists('env')) {
    /**
     * 判断当前项目环境，或获取当前项目环境
     *
     * @param  string           $asset
     * @return boolean|string
     */
    function env($environment = null)
    {
        if ($environment === null) {
            return app('settings')['environment'];
        }

        return strtolower($environment) === app('settings')['environment'];
    }
}

if (!function_exists('cache')) {
    /**
     * 使用 psr-16 simple cache 进行缓存管理
     *
     * @param  dynamic      key|key,default|data,expiration|null
     * @throws \Exception
     * @return mixed
     */
    function cache()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            return app('cache.simple');
        }

        if (is_string($arguments[0])) {
            return app('cache.simple')->get($arguments[0], isset($arguments[1]) ? $arguments[1] : null);
        }

        if (!is_array($arguments[0])) {
            throw new Exception(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        return app('cache.simple')->set(
            key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
    }
}

if (!function_exists('config')) {
    /**
     * 获取配置数据
     *
     * @param  array   $key
     * @param  mixed   $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if ($key === null) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('cookie')) {
    /**
     * 获取或设定 cookie 值
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int     $minutes
     * @return mixed
     */
    function cookie($name = null, $value = null, $minutes = null)
    {
        if (func_num_args() === 0) {
            return app('cookies');
        }

        if (func_num_args() === 1) {
            return app('cookies')->get($name);
        }

        return app('cookies')->set($name, $value, $minutes);
    }
}

if (!function_exists('session')) {
    /**
     * 获取或设定 session 值
     *
     * @param  array|string $key
     * @param  mixed        $default
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->set($key);
        }

        return app('session')->get($key, $default);
    }
}

if (!function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string                                                $id
     * @param  array                                                 $replace
     * @param  string                                                $locale
     * @return \Illuminate\Contracts\Translation\Translator|string
     */
    function trans($id = null, $replace = [], $locale = null) {}
}

if (!function_exists('trans_choice')) {
    /**
     * Translates the given message based on a count.
     *
     * @param  string               $id
     * @param  int|array|\Countable $number
     * @param  array                $replace
     * @param  string               $locale
     * @return string
     */
    function trans_choice($id, $number, array $replace = [], $locale = null)
    {
        return app('translator')->transChoice($id, $number, $replace, $locale);
    }
}

if (!function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param  string                                                $key
     * @param  array                                                 $replace
     * @param  string                                                $locale
     * @return \Illuminate\Contracts\Translation\Translator|string
     */
    function __($key = null, $replace = [], $locale = null) {}
}

if (!function_exists('view')) {
    /**
     * 获取视图对象，或渲染视图模板
     *
     * @param  string                 $view
     * @param  array                  $data
     * @param  callable|null          $callback
     * @return \Mellivora\View\View
     */
    function view($view = null, $data = [], callable $callback = null)
    {
        if (func_num_args() === 0) {
            return app('view');
        }

        return app('view')->make($view, $data)->render($callback);
    }
}

if (!function_exists('redirect')) {
    /**
     * 重定向当前页面，response http 302 header
     *
     * @param  string                    $to
     * @param  int                       $status
     * @return Mellivora\Http\Response
     */
    function redirect($to, $status = null)
    {
        return app('response')->redirect($to, $status);
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前 http request 输入的数据
     *
     * @param  array|string                          $key
     * @param  mixed                                 $default
     * @return Mellivora\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        return data_get(app('request')->all(), $key, $default);
    }
}

if (!function_exists('response')) {
    /**
     * 返回 http response 的结果
     *
     * @param  string                    $content
     * @param  int                       $status
     * @param  array                     $headers
     * @return Mellivora\Http\Response
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        if (func_num_args() === 0) {
            return app('response');
        }

        return app('response')->withStatus($status)
            ->withHeaders($headers)
            ->write($content);
    }
}

if (!function_exists('route')) {
    /**
     * 根据路由参数，生成 route url
     *
     * @param  string   $name        Route name
     * @param  array    $data        Named argument replacement data
     * @param  array    $queryParams Optional query string parameters
     * @return string
     */
    function route($name, array $data = [], array $queryParams = [])
    {
        return app('router')->pathFor($name, $data, $queryParams);
    }
}

if (!function_exists('url')) {
    /**
     * URL 生成器，可通过指定 params 来生成 query string
     *
     * @param  string   $path
     * @param  array    $queryParams
     * @return string
     */
    function url($path = null, array $queryParams = [])
    {
        if ($path === null) {
            return app('request')->fullUrlWithQuery($queryParams);
        }

        if (preg_match('~^https?://~', $path)) {
            $uri = Uri::createFromString($path);
        } else {
            $parts = parse_url($path) + ['path' => '', 'query' => '', 'fragment' => ''];

            $uri = app('request')->getUri()
                ->withPath($parts['path'])
                ->withQuery($parts['query'])
                ->withFragment($parts['fragment']);
        }

        parse_str($uri->getQuery(), $queryParts);

        return (string) $uri->withQuery(http_build_query($queryParams + $queryParts));
    }
}

if (!function_exists('url_spintf')) {
    /**
     * 使用 sprintf 格式化生成 url
     *
     * @param  string   $format
     * @param  string   ...$args
     * @return string
     */
    function url_spintf($format, ...$args)
    {
        return url(vsprintf($format, $args));
    }
}
if (!function_exists('root_path')) {
    /**
     * 获取项目根目录下的路径
     *
     * @param  string   $path
     * @return string
     */
    function root_path($path = '')
    {
        return normalize_path(__ROOT__ . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('app_path')) {
    /**
     * 获取 app 目录下的路径
     *
     * @param  string   $path
     * @return string
     */
    function app_path($path = '')
    {
        return root_path('app/' . $path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * 获取 storage 存储目录下的路径
     *
     * @param  string   $path
     * @return string
     */
    function storage_path($path = '')
    {
        return root_path('storage/' . $path);
    }
}

if (!function_exists('resource_path')) {
    /**
     * 获取 resources 资源目录下的路径
     *
     * @param  string   $path
     * @return string
     */
    function resource_path($path = '')
    {
        return root_path('resources/' . $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取 public 网站根目录下的路径
     *
     * @param  string   $path
     * @return string
     */
    function public_path($path = '')
    {
        return root_path('public/' . $path);
    }
}

if (!function_exists('array_add')) {
    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}

if (!function_exists('array_collapse')) {
    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  array   $array
     * @return array
     */
    function array_collapse($array)
    {
        return Arr::collapse($array);
    }
}

if (!function_exists('array_divide')) {
    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array   $array
     * @return array
     */
    function array_divide($array)
    {
        return Arr::divide($array);
    }
}

if (!function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function array_dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }
}

if (!function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array        $array
     * @param  array|string $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}

if (!function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array         $array
     * @param  callable|null $callback
     * @param  mixed         $default
     * @return mixed
     */
    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}

if (!function_exists('array_flatten')) {
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array   $array
     * @param  int     $depth
     * @return array
     */
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}

if (!function_exists('array_forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array        $array
     * @param  array|string $keys
     * @return void
     */
    function array_forget(&$array, $keys)
    {
        return Arr::forget($array, $keys);
    }
}

if (!function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array $array
     * @param  string             $key
     * @param  mixed              $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (!function_exists('array_has')) {
    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  \ArrayAccess|array $array
     * @param  string|array       $keys
     * @return bool
     */
    function array_has($array, $keys)
    {
        return Arr::has($array, $keys);
    }
}

if (!function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array         $array
     * @param  callable|null $callback
     * @param  mixed         $default
     * @return mixed
     */
    function array_last($array, callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}

if (!function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     *
     * @param  array        $array
     * @param  array|string $keys
     * @return array
     */
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}

if (!function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param  array             $array
     * @param  string|array      $value
     * @param  string|array|null $key
     * @return array
     */
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (!function_exists('array_prepend')) {
    /**
     * Push an item onto the beginning of an array.
     *
     * @param  array   $array
     * @param  mixed   $value
     * @param  mixed   $key
     * @return array
     */
    function array_prepend($array, $value, $key = null)
    {
        return Arr::prepend($array, $value, $key);
    }
}

if (!function_exists('array_pull')) {
    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}

if (!function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}

if (!function_exists('array_sort')) {
    /**
     * Sort the array using the given callback.
     *
     * @param  array    $array
     * @param  callable $callback
     * @return array
     */
    function array_sort($array, callable $callback)
    {
        return Arr::sort($array, $callback);
    }
}

if (!function_exists('array_sort_recursive')) {
    /**
     * Recursively sort an array by keys and values.
     *
     * @param  array   $array
     * @return array
     */
    function array_sort_recursive($array)
    {
        return Arr::sortRecursive($array);
    }
}

if (!function_exists('array_where')) {
    /**
     * Filter the array using the given callback.
     *
     * @param  array    $array
     * @param  callable $callback
     * @return array
     */
    function array_where($array, callable $callback)
    {
        return Arr::where($array, $callback);
    }
}

if (!function_exists('array_wrap')) {
    /**
     * If the given value is not an array, wrap it in one.
     *
     * @param  mixed   $value
     * @return array
     */
    function array_wrap($value)
    {
        return Arr::wrap($value);
    }
}

if (!function_exists('array_convert')) {
    /**
     * Convert object to array
     *
     * @param  mixed   $object
     * @param  boolean $recursive
     * @return array
     */
    function array_convert($object, $recursive = false)
    {
        return Arr::convert($object, $recursive);
    }
}

if (!function_exists('camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param  string   $value
     * @return string
     */
    function camel_case($value)
    {
        return Str::camel($value);
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('normalize_path')) {
    /**
     * Normalized the path string.
     *
     * @param  string   $path
     * @return string
     */
    function normalize_path($path)
    {
        return preg_replace('/[\\\\\/]+/', DIRECTORY_SEPARATOR, (string) $path);
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its subclasses and trait of their traits.
     *
     * @param  object|string $class
     * @return array
     */
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_merge([$class => $class], class_parents($class)) as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (!function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed                           $value
     * @return \Mellivora\Support\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (!function_exists('data_fill')) {
    /**
     * Fill in data where it's missing.
     *
     * @param  mixed        $target
     * @param  string|array $key
     * @param  mixed        $value
     * @return mixed
     */
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed        $target
     * @param  string|array $key
     * @param  mixed        $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return value($default);
                }

                $result = Arr::pluck($target, $key);

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed        $target
     * @param  string|array $key
     * @param  mixed        $value
     * @param  bool         $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd(...$args)
    {
        foreach ($args as $x) {
            var_dump($x);
        }

        die(1);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param  string   $value
     * @return string
     */
    function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}

if (!function_exists('head')) {
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array   $array
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}

if (!function_exists('kebab_case')) {
    /**
     * Convert a string to kebab case.
     *
     * @param  string   $value
     * @return string
     */
    function kebab_case($value)
    {
        return Str::kebab($value);
    }
}

if (!function_exists('last')) {
    /**
     * Get the last element from an array.
     *
     * @param  array   $array
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}

if (!function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param  object  $object
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (!function_exists('preg_replace_array')) {
    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param  string   $pattern
     * @param  array    $replacements
     * @param  string   $subject
     * @return string
     */
    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}

if (!function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param  int          $times
     * @param  callable     $callback
     * @param  int          $sleep
     * @throws \Exception
     * @return mixed
     */
    function retry($times, callable $callback, $sleep = 0)
    {
        $times--;

        beginning:
        try {
            return $callback();
        } catch (Exception $e) {
            if (!$times) {
                throw $e;
            }

            $times--;

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (!function_exists('snake_case')) {
    /**
     * Convert a string to snake case.
     *
     * @param  string   $value
     * @param  string   $delimiter
     * @return string
     */
    function snake_case($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }
}

if (!function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (!function_exists('str_contains')) {
    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    function str_contains($haystack, $needles)
    {
        return Str::contains($haystack, $needles);
    }
}

if (!function_exists('str_finish')) {
    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string   $value
     * @param  string   $cap
     * @return string
     */
    function str_finish($value, $cap)
    {
        return Str::finish($value, $cap);
    }
}

if (!function_exists('str_is')) {
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string $pattern
     * @param  string $value
     * @return bool
     */
    function str_is($pattern, $value)
    {
        return Str::is($pattern, $value);
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limit the number of characters in a string.
     *
     * @param  string   $value
     * @param  int      $limit
     * @param  string   $end
     * @return string
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        return Str::limit($value, $limit, $end);
    }
}

if (!function_exists('str_plural')) {
    /**
     * Get the plural form of an English word.
     *
     * @param  string   $value
     * @param  int      $count
     * @return string
     */
    function str_plural($value, $count = 2)
    {
        return Str::plural($value, $count);
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int                 $length
     * @throws \RuntimeException
     * @return string
     */
    function str_random($length = 16)
    {
        return Str::random($length);
    }
}

if (!function_exists('str_replace_array')) {
    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param  string   $search
     * @param  array    $replace
     * @param  string   $subject
     * @return string
     */
    function str_replace_array($search, array $replace, $subject)
    {
        return Str::replaceArray($search, $replace, $subject);
    }
}

if (!function_exists('str_replace_first')) {
    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param  string   $search
     * @param  string   $replace
     * @param  string   $subject
     * @return string
     */
    function str_replace_first($search, $replace, $subject)
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (!function_exists('str_replace_last')) {
    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param  string   $search
     * @param  string   $replace
     * @param  string   $subject
     * @return string
     */
    function str_replace_last($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (!function_exists('str_singular')) {
    /**
     * Get the singular form of an English word.
     *
     * @param  string   $value
     * @return string
     */
    function str_singular($value)
    {
        return Str::singular($value);
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string   $title
     * @param  string   $separator
     * @return string
     */
    function str_slug($title, $separator = '-')
    {
        return Str::slug($title, $separator);
    }
}

if (!function_exists('studly_case')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param  string   $value
     * @return string
     */
    function studly_case($value)
    {
        return Str::studly($value);
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed         $value
     * @param  callable|null $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if (!function_exists('title_case')) {
    /**
     * Convert a value to title case.
     *
     * @param  string   $value
     * @return string
     */
    function title_case($value)
    {
        return Str::title($value);
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param  string  $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed   $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('windows_os')) {
    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    function windows_os()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}
