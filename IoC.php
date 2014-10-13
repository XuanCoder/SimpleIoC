<?php namespace SimpleIoC;

    class IoC{

        const INSTANCE = 1; //实例化的对象类型
        const CLOSURE = 2; //匿名函数类型
        const CLASSINFO = 3; //对象信息

        /**
         * Record key and type
         * @var Array
         */
        private static $record = [];

        /**
         * Store Instance
         * @var Array
         */
        private static $instances = [];

        /**
         * Store Closure
         * @var Array
         */
        private static $closures = [];

        /**
         * Store ClassInfo
         * @var Array
         */
        private static $classesinfo = [];


        /**
         * 向DI容器中添加对象或方法
         * Add object to container
         * @param String $name The name of key
         * @param [type] $cont [description]
         */
        public static function set($name, $cont = null){

            if(!is_string($name)){
                throw new \Exception("Key must be String", 1);
            }

            // Closure
            if($cont instanceof Closure){
                self::$closures[$name] = $cont;
                self::$record[$name] = self::CLOSURE;
            }

            // Instance
            elseif(is_object($cont)){
                self::$instances[$name] = $cont;
                self::$record[$name] = self::INSTANCE;
            }

            // Class info
            elseif (is_array($cont)) {
                if(!is_string($cont['ClassName'])){
                    throw new \Exception("Classname must be String");
                }elseif (!is_array($cont['param'])) {
                    $cont['param'] = [];
                }
                
                self::$classesinfo[$name] = $cont;
                self::$record[$name] = self::CLASSINFO;
            }
            
            else{
                throw new \Exception('Type Error');
            }
        }

        /**
         * 从DI容器中获取
         * 
         * @param  String $name [description]
         */
        public static function make($name){

            if(!isset(self::$record[$name])){
                return static::makeUnknowKey($name);
            }

            switch (self::$record[$name]) {
                
                case self::INSTANCE:
                    return self::$instances[$name];

                case self::CLOSURE:
                    $closure = self::$closures[$name];
                    return $closure();
                
                case self::CLASSINFO:
                    $info = self::$classesinfo[$name];
                    $ref = new \ReflectionClass($info['ClassName']); // 获取类的反射对象
                    return $ref->newInstanceArgs($info['param']); // 用参数构造实例后返回

                default:
                    throw new \Exception('Unknow Exception');
            }
        }

        /**
         * 当make不存在的Key时,尝试将key作为类名实例化
         * 
         * @return [type] [description]
         */
        private static function makeUnknowKey($key){
            //TODO: 自动注册这个类的依赖
            $ref = new \ReflectionClass($key); // 获取类的反射对象
            return $ref->newInstance();
        }
    }