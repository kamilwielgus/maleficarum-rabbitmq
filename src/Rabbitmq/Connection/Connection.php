<?php
/**
 * This class represents a connection to a RabbitMQ broker.
 */
declare (strict_types=1);

namespace Maleficarum\Rabbitmq\Connection;

class Connection {
    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Internal storage for a AMQP connection object.
     *
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection|null
     */
    private $connection = null;

    /**
     * Internal storage for queue name.
     *
     * @var string
     */
    private $queueName;

    /**
     * Internal storage for host.
     *
     * @var string
     */
    private $host;

    /**
     * Internal storage for port.
     *
     * @var int
     */
    private $port;

    /**
     * Internal storage for username.
     *
     * @var string
     */
    private $username;

    /**
     * Internal storage for password.
     *
     * @var string
     */
    private $password;

    /**
     * Internal storage for the vhost parameter.
     * 
     * @var string
     */
    private $vhost;

    /* ------------------------------------ Class Property END ----------------------------------------- */

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Connection constructor.
     *
     * @param string $queueName
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $vhost
     */
    public function __construct(string $queueName, string $host, int $port, string $username, string $password, string $vhost = '/') {
        $this->setQueueName($queueName);
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost = $vhost;
    }

    /**
     * Perform connection cleanup.
     */
    public function __destruct() {
        $this->disconnect();
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ Class Methods START ---------------------------------------- */
    
    /**
     * Initialize this object.
     *
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function connect() : \Maleficarum\Rabbitmq\Connection\Connection {
        is_null($this->getConnection()) || !$this->getConnection()->isConnected() and $this->setConnection(\Maleficarum\Ioc\Container::get(
            'PhpAmqpLib\Connection\AMQPStreamConnection', 
            [$this->host, $this->port, $this->username, $this->password, $this->vhost]
        ));

        return $this;
    }

    /**
     * Close connection and channel.
     *
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function disconnect() : \Maleficarum\Rabbitmq\Connection\Connection {
        $this->getConnection() && $this->getConnection()->isConnected() and $this->getConnection()->close();

        return $this;
    }

    /**
     * Fetch the communications channel. This will be useful when executing chitinous command fetching in worker scripts.
     *
     * @param string $id
     * @throws \InvalidArgumentException
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel(int $id = null) : \PhpAmqpLib\Channel\AMQPChannel {
        // validate channel id 
        if ($id <= 0 && !is_null($id)) throw new \InvalidArgumentException(sprintf('Channel ID must be a positive integer value. %s', __METHOD__));
        
        return $this->getConnection()->channel($id);
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */

    /* ------------------------------------ Setters & Getters START ------------------------------------ */

    /**
     * Set current AMQP queue connection.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    private function setConnection(\PhpAmqpLib\Connection\AMQPStreamConnection $connection) : \Maleficarum\Rabbitmq\Connection\Connection {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Fetch current AMQP queue connection.
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection|null
     */
    public function getConnection() :? \PhpAmqpLib\Connection\AMQPStreamConnection {
        return $this->connection;
    }
    
    /**
     * Fetch the current queue name used by this connection.
     * 
     * @return string
     */
    public function getQueueName() :? string {
        return $this->queueName;
    }

    /**
     * Set the current queue name used by this connection.
     * 
     * @param string $queueName
     * @return \Maleficarum\Rabbitmq\Connection\Connection
     */
    public function setQueueName(string $queueName) : \Maleficarum\Rabbitmq\Connection\Connection {
        $this->queueName = $queueName;
        return $this;
    }
    
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
