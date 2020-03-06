<?php
/**
 * Created by PhpStorm.
 * User: leeyifiei
 * Date: 2020/3/6
 * Time: 10:58 AM
 */

namespace qianfan\consul;


use DCarbone\PHPConsulAPI\Config;
use DCarbone\PHPConsulAPI\Consul;
use yii\base\Component;
use yii\caching\Cache;
use yii\di\Instance;
use yii\web\HttpException;

class Client extends Component
{
    public $address;

    public $scheme;

    public $datacenter;

    public $httpAuth;

    public $token;

    public $tokenInHeader;

    public $insecureSkipVerify;

    public $cacheComponent;

    /**
     * @var Consul $_factory
     */
    private $_factory;

    /**
     * @var Cache $_cache
     */
    private $_cache;

    public function init()
    {
        parent::init();

        $config = new Config([
            'Address' => $this->address,
            'Scheme' => $this->scheme,
            'Datacenter' => $this->datacenter,
            // 'HttpAuth' => $this->httpAuth,
            'Token' => $this->token,
            'TokenInHeader' => $this->tokenInHeader,
            'InsecureSkipVerify' => $this->insecureSkipVerify
        ]);

        $this->_factory = new Consul($config);
        $this->_cache = Instance::ensure($this->cacheComponent, Cache::class);
    }

    public function discover($serviceName, $tag = '', $passingOnly = true)
    {
        $cacheName = __METHOD__ . '::' . $serviceName;
        $services = $this->_cache->get($cacheName);

        if (!$services) {
            $health = $this->_factory->Health();

            if (!isset($filter['passing'])) {
                $filter['passing'] = true;
            }
            list($services, $qm, $err) = $health->service($serviceName, $tag, $passingOnly);

            if ($services) {
                $this->_cache->set($cacheName, $services, 600);
            }
        }

        $result = null;
        if ($services) {
            $k = array_rand($services);
            $service = $services[$k];


            $url = $service->Service->Address . ':' . $service->Service->Port;
            $result = [$url, $service];
        } else {
            throw new HttpException(404, "no available $serviceName service");
        }

        return $result;
    }
}