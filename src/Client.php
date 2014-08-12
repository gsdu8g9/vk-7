<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Vk;

class Client
{
    /**
     * Application secret
     *
     * @var string|integer
     */
    protected $appId;

    /**
     * Application secret
     *
     * @var string
     */
    protected $appSecret;

    /**
     * @var \Guzzle\Http\Client
     */
    protected $httpClient;

    protected $baseParameters = array(
        'v' => 5.24
    );

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;

        $this->httpClient = new \Guzzle\Http\Client('https://api.vk.com/');
    }

    /**
     * Request social server api
     *
     * @param $uri
     * @param array $parameters
     * @return bool
     * @throws Exception
     */
    public function request($uri, array $parameters = array())
    {
        $parameters = array_merge($this->baseParameters, $parameters);

        $request = $this->httpClient->get($uri.'?'.http_build_query($parameters));
        $response = $request->send();

        if ($response) {
            if ($response->isServerError()) {
                throw new Exception('Server error');
            }

            $body = $response->getBody(true);
            if ($body) {
                $json = json_decode($body);

                if (isset($json->response)) {
                    return $json->response;
                } else {
                    throw new Exception('Error 1');
                }
            } else {
                throw new Exception('Error 2');
            }
        }

        return false;
    }

    protected $hydrator;

    public function getHydrator()
    {
        if (!$this->hydrator) {
            return $this->hydrator = new \SocialConnect\Common\Hydrator\ObjectMap(array(
                'id' => 'id',
                'first_name' => 'firstname',
                'last_name' => 'lastname'
            ));
        }

        return $this->hydrator;
    }

    /**
     * @param $id
     * @return bool
     */
    public function getUser($id)
    {
        $result = $this->request('method/getProfiles', array(
            'user_id' => $id
        ));

        if ($result) {
            $result = $result[0];

            return $this->getHydrator()->hydrate(new Entity\User(), $result);
        }

        return false;
    }

    public function getUsers(array $ids)
    {
        if (count($ids) == 0) {
            return false;
        }

        $apiResult = $this->request('method/getProfiles', array(
            'uids' => $ids
        ));

        if ($apiResult && is_array($apiResult)) {
            $result = array();

            foreach ($apiResult as $row) {
                $result[] = $this->getHydrator()->hydrate(new Entity\User(), $result);
            }

            return $result;
        }

        return false;
    }
}
