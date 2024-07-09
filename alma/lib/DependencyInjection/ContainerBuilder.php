<?php

namespace Alma\PrestaShop\DependencyInjection;

use Alma\PrestaShop\Exceptions\DependencyInjectionException;
use ReflectionClass;

class ContainerBuilder
{
    /**
     * @var array - Tableau sauvegardant les dépendances
     */
    public $registry = [];
    /**
     * @var array - Tableau sauvegardant les dépendances (Factory)
     */
    public $factories = [];
    /**
     * @var array - Tableau sauvegardant les dépendances (Singleton)
     */
    public $instances = [];

    /**
     * Sauvegarde les dépendances dans le tableau $registry
     *
     * @param $key string Nom de la classe
     * @param $resolver callable Fonction
     */
    public function set($key, $resolver)
    {
        $this->registry[$key] = $resolver;
    }

    /**
     * @param $key
     *
     * @return mixed
     *
     * @throws DependencyInjectionException
     */
    public function get($key)
    {
        if (!isset($this->instances[$key])) {
            if (isset($this->registry[$key])) {
                $this->instances[$key] = $this->registry[$key]($this);
            } else {
                throw new DependencyInjectionException($key . " n'est pas dans mon conteneur :(");
            }
        }

        return $this->instances[$key];
    }

    /**
     * Sauvegarde les dépendances dans le tableau $factories (Factory)
     *
     * @param $key string Nom de la classe
     * @param $resolver callable Fonction
     */
    public function setFactory($key, $resolver)
    {
        $this->factories[$key] = $resolver;
        var_dump($this->factories[$key]);
    }

    /**
     * Sauvegarde les dépendances dans le tableau $instances (Singleton)
     *
     * @throws \ReflectionException
     */
    public function setInstance($instance)
    {
        $reflection = new ReflectionClass($instance);
        $this->instances[$reflection->getName()] = $instance;
    }

    /**
     * Permet de récupérer les dépendances pour créer un singleton
     *
     * @throws \ReflectionException
     * @throws DependencyInjectionException
     */
    public function getInstance($key)
    {
        if (!isset($this->instances[$key])) {
            if (isset($this->registry[$key])) {
                $this->instances[$key] = $this->registry[$key]();
            } else {
                $reflected_class = new ReflectionClass($key);
                if ($reflected_class->isInstantiable()) {
                    $constructor = $reflected_class->getConstructor();
                    //var_dump($reflected_class->getConstructor());
                    if ($constructor) {
                        $parameters = $constructor->getParameters();
                        $constructor_parameters = [];
                        foreach ($parameters as $parameter) {
                            if ($parameter->getClass()) {
                                $constructor_parameters[] = $this->get($parameter->getClass()->getName());
                            } else {
                                $constructor_parameters[] = $parameter->getName();
                            }
                        }
                        $this->instances[$key] = $reflected_class->newInstanceArgs($constructor_parameters);
                    } else {
                        $this->instances[$key] = $reflected_class->newInstance();
                    }
                } else {
                    throw new DependencyInjectionException('"' . $key . '" is not an instantiable class');
                }
            }
        }

        return $this->instances[$key];
    }

    /**
     * @param $key
     *
     * @return mixed|object|null
     *
     * @throws \ReflectionException
     * @throws DependencyInjectionException
     */
    public function getFactory($key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        } else {
            if (isset($this->registry[$key])) {
                $this->factories[$key] = $this->registry[$key]();
            } else {
                $reflectedClass = new ReflectionClass($key);
                if ($reflectedClass->isInstantiable()) {
                    $constructor = $reflectedClass->getConstructor();
                    if ($constructor) {
                        $parameters = $constructor->getParameters();
                        $constructorParameters = [];
                        //var_dump($parameters);
                        foreach ($parameters as $parameter) {
                            //var_dump($parameter->getClass());
                            if ($parameter->getClass()) {
                                $constructorParameters[] = $this->getFactory($parameter->getClass()->getName());
                            } else {
                                $constructorParameters[] = $parameter->getDefaultValue();
                            }
                        }
                        $this->factories[$key] = $reflectedClass->newInstanceArgs($constructorParameters);
                    } else {
                        $this->factories[$key] = $reflectedClass->newInstance();
                    }
                } else {
                    throw new DependencyInjectionException('"' . $key . '" is not an instantiable class');
                }
            }
        }

        return $this->factories[$key];
    }
}
