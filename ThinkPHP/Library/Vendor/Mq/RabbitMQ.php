<?php
final class RabbitMQ{
//服务器地址
private $_host;
//服务端口
private $_port;
//路由名称
private $_rout;
//用户名
private $_login;
//密码
private $_password;
//虚拟机名称
private $_vhost ;
//交换机名称
private $_exchangename;
//交换机flags
private $_flags;
//队列名称
private $_queue;
//当前类对象
private static $_obj;
//MQ服务对象
private $_MQ;
//MQ通道对象
private $_channel;
//MQ路由对象
private $_exchange;
//MQ队列对象
private $_queueobj;
private $_queueFlags;
private function __construct(){

}

private function __clone(){

}
/**
* 配置文件设置
* @param array
*/
public function config($param){
$this->_host = $param["host"];
$this->_port = $param["port"];
$this->_login = $param["user_name"];
$this->_password = $param["password"];
$this->_queue = $param["queue_name"];
$this->_route_key = $param["route_key"];
$this->_exchangename = $param["exchangename"];
$this->_vhost = $param["vhost"];
$this->_exchangeType = $param["exchangeType"];
$this->_exchangeFlags = $param["flags"];
$this->_queueFlags = $param["flags"];
}

/**
* 获取当前类对象实现单例
*/
public static function init(){
if(!self::$_obj instanceof self){
self::$_obj = new self;
}

return self::$_obj;
}

public function connect(){
$config = array(
"host" => $this->_host,
"port" => $this->_port,
"login" => $this->_login,
"password" => $this->_password,
"vhost" => $this->_vhost,
);
//创建服务器链接对象
$this->_MQ = new AMQPConnection($config);
if (!$this->_MQ->connect()) {
throw new Exception("链接MQ服务失败", 400);
}
//创建通道链接对象
$this->_channel = new AMQPChannel($this->_MQ);
//创建路由对象
$this->_exchange = new AMQPExchange($this->_channel);
//设置交换机名称
if(!empty($this->_exchangename))
$this->_exchange->setName($this->_exchangename);
//设置交换机类型
if(!empty($this->_exchangeType))
$this->_exchange->setType($this->_exchangeType);
//设置交换机flags
if(!empty($this->_exchangeFlags))
$this->_exchange->setFlags($this->_exchangeFlags);
//创建交换机
$this->_exchange->declareExchange();
//创建队列对象
$this->_queueobj = new AMQPQueue($this->_channel);
//设置队列名称
$this->_queueobj ->setName($this->_queue);
//设置队列flags;
$this->_queueobj->setFlags($this->_queueFlags);
//创建队列
$this->_queueobj ->declareQueue();
//将队列和交换机绑定道路由key
$this->_queueobj ->bind($this->_exchangename,$this->_route_key);

}

/**
* 发布消息
*/
public function publish($content){
	$this->_exchange->publish($content,$this->_route_key);
}

/**
* 获取消息
*/
public function getMsg(){
	$object = $this->_queueobj->get(AMQP_AUTOACK);
        if($object)
        $info = $object->getBody();
	/*$func_msg = function ($envelope, $queue){
		$msg = $envelope->getBody();  
		echo $msg."\n"; //处理消息  
		$queue->ack($envelope->getDeliveryTag()); //手动发送ACK应答  
	};
	$info = $this->_queueobj->consume($func_msg);*/
	return $info;
}


}