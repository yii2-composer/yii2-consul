<?php
/**
 * Created by PhpStorm.
 * User: leeyifiei
 * Date: 2020/3/6
 * Time: 10:58 AM
 */

namespace qianfan\consul;


use SensioLabs\Consul\ServiceFactory;
use SensioLabs\Consul\Services\Health;
use SensioLabs\Consul\Services\HealthInterface;
use yii\base\Component;
use yii\caching\Cache;
use yii\di\Instance;
use yii\web\HttpException;

class client extends Component
{
    public $options;

    public $cacheComponent;

    /**
     * @var ServiceFactory $_factory
     */
    private $_factory;

    /**
     * @var Cache $_cache
     */
    private $_cache;

    public function init()
    {
        parent::init();

        $this->_factory = new ServiceFactory($this->options);
        $this->_cache = Instance::ensure($this->cacheComponent, Cache::class);
    }

    public function discover($serviceName, $filter = [])
    {
        $cacheName = __METHOD__ . '::' . $serviceName;
        $services = $this->_cache->get($cacheName);

        if (!$services) {
            /**
             * @var Health $health
             */
            $health = $this->_factory->get(HealthInterface::class);

            if (!isset($filter['passing'])) {
                $filter['passing'] = true;
            }
            $result = $health->service($serviceName, $filter);
            $services = $result->json();

            if ($services) {
                $this->_cache->set($cacheName, $services, 600);
            }
        }

        $result = null;
        if (count($services) > 0) {
            $k = array_rand($services);
            $service = $services[$k];

            $url = $service['Service']['Address'] . ':' . $service['Service']['Port'];

            $result = [$url, $service];
        } else {
            throw new HttpException(404, "no available $serviceName service");
        }

        return $result;
    }
}