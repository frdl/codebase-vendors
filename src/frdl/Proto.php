<?php

namespace frdl;
/**
 * Base class for creating dynamic objects
 *
 * @author Petr Trofimov <petrofimov@yandex.ru>
 * @see https://github.com/ptrofimov/jslikeobject
 */
class Proto
{
    protected $properties = [];
	
    public static function create($arg)
    {
	$class = \get_called_class();
        return new $class(\is_callable($arg) ? ['constructor' => $arg] : (array) $arg);
    }
	
    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    public function __get($key)
    {
        $value = null;
        if (array_key_exists($key, $this->properties)) {
            $value = $this->properties[$key];
        } elseif (isset($this->properties['prototype'])) {
            $value = $this->properties['prototype']->{$key};
        }

        return $value;
    }

    public function __set($key, $value)
    {
        $this->properties[$key] = $value;
    }

    public function __call($method, array $args)
    {
		$____method = '____'.$method; 
		
        return (is_callable($this->{$method})
            ? call_user_func_array(
                $this->{$method}->bindTo($this),
                $args
            ) : ( method_exists($this,$____method) 
            ? call_user_func_array(
                [$this,$____method],
                $args
            ) : null));
    }

    public function __invoke(...$args)
    {
	$class = \get_class($this);
        $instance = new $class($this->properties);
        if ($this->constructor) {
            $instance->constructor(...$args);
        }

        return $instance;
    }
	
    public function ____extend(...$args)
    {      
	$this->prototype = static::create(...$args);
        if ($this->constructor) {
            $this->constructor(...$args);
        }
	   return $this;
    }
}	
