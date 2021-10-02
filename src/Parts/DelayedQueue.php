<?php

namespace Merexo\Rediska\Parts;

class DelayedQueue extends RedisPart
{
    /**
     * @param string $queue_name
     * @param $payload
     * @param int $delay
     * @return mixed
     */
    public function push(string $queue_name, $payload, int $delay = 180)
    {
        return $this->redis->rawCommand('ZADD', $queue_name, 'NX', time() + $delay, serialize($payload));
    }

    /**
     * @param string $queue_name
     * @return mixed
     */
    public function pop(string $queue_name)
    {
        $command = 'eval "
                local val = redis.call(\'ZRANGEBYSCORE\', KEYS[1], 0, ARGV[1], \'LIMIT\', 0, 1)[1]
                if val then
                    redis.call(\'ZREM\', KEYS[1], val)
                end
                return val"
        ';

        return $this->redis->rawCommand($command, 1, $queue_name, time());
    }
}