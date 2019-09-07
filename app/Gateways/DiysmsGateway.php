<?php
/**
 * Created by PhpStorm.
 * User: Wild&Cat~
 * Date: 2019/3/20
 * Time: 19:00
 */

namespace App\Gateways;


use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

/**
 * Class DiysmsGateway.
 *
 * @see http://119.29.200.194:6687
 */
class DiysmsGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://119.29.200.194:6687/sms.aspx';

    const RESOK = 'Success';
    const MSGOK = 'ok';
    /**
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface     $message
     * @param \Overtrue\EasySms\Support\Config                 $config
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\GatewayErrorException ;
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {


        $params = [
            'action' => 'send',
            'userid' => env('DIYSMS_UID'),
            'account' => env('DIYSMS_ACCOUNT'),
            'password' => env('DIYSMS_PWD'),
            'mobile' => $to->getNumber(),
            'content' => '【' . env('DIYSMS_KEY') . '】'.$message->getContent() ,
        ];

        $result = $this->get(self::ENDPOINT_URL, $params);

        if ($result['returnstatus'] == self::RESOK || $result['message'] == self::MSGOK) {
            return $result;
        }

        throw new GatewayErrorException($result['message'], $result['returnstatus'], $result);
    }

    /**
     * @param array $vars
     *
     * @return string
     */
    protected function formatTemplateVars(array $vars)
    {
        $formatted = [];

        foreach ($vars as $key => $value) {
            $formatted[sprintf('#%s#', trim($key, '#'))] = $value;
        }

        return http_build_query($formatted);
    }

    protected function xmlToArray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}


//use Overtrue\EasySms\EasySms;
//
//class Diysms extends EasySms
//{
//
//    /**
//     * Send a short message.
//     *
//     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
//     * @param \Overtrue\EasySms\Contracts\MessageInterface $message
//     * @param \Overtrue\EasySms\Support\Config $config
//     *
//     * @return array
//     */
//    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
//    {
//        $params = [
//            'action' => 'send',
//            'userid' => $config->get('uid'),
//            'account' => $config->get('account'),
//            'password' => $config->get('pwd'),
//            'mobile' => $to->getNumber(),
//            'content' => '【' . $config->get('sign') . '】'.$message->getContent() ,
//        ];
//        dd($params);
//
//        return $this->get(self::ENDPOINT_URI, $params);
//    }
//
//    protected function getBaseUri()
//    {
//        return self::ENDPOINT_HOST;
//    }
//}
