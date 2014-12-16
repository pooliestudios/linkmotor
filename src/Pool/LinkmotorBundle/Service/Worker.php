<?php
namespace Pool\LinkmotorBundle\Service;

class Worker
{
    /**
     * @var int $timeToLive seconds after which lock will be forcefully removed
     */
    protected $timeToLive = 180;

    /**
     * @var
     */
    protected $logger;

    /**
     * @var string $token
     */
    protected $token;

    /**
     * @var int $updates;
     */
    protected $updates;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function __destruct()
    {
        $this->end();
    }

    public function start($token)
    {
        $this->token = $token;

        return $this->getLock();
    }

    public function end()
    {
        if ($this->token) {
            @unlink($this->getLockFilename());
        }
    }

    public function getUpdates()
    {
        return $this->updates;
    }

    public function update()
    {
        $lockFilename = $this->getLockFilename();
        if (!file_exists($lockFilename)) {
            $this->logger->err('Lockfile no longer exists! (' . getmypid() . ' / ' . $this->token . ')');

            return false;
        }

        $lockContent = file_get_contents($lockFilename);
        $lockData = json_decode($lockContent, true);
        $lockData['heartbeat'] = date('Y-m-d H:i:s');
        $lockData['updates']++;
        $this->updates = $lockData['updates'];

        file_put_contents($lockFilename, json_encode($lockData));

        return true;
    }

    protected function getLock()
    {
        $lockFilename = $this->getLockFilename();
        if (file_exists($lockFilename)) {
            $lockContent = file_get_contents($lockFilename);
            $lockData = json_decode($lockContent, true);
            $endOfLifeTimestamp = date('Y-m-d H:i:s', strtotime('-' . $this->timeToLive . ' seconds'));
            if ($lockData['heartbeat'] < $endOfLifeTimestamp) {
                system('kill -9 ' . $lockData['pid']);
                $this->logger->err('Killing ' . $lockData['pid'] . ' ( ' . $lockData['heartbeat'] . ')');
            } else {
                $this->token = null;

                return false;
            }
        }
        $this->updates = 0;
        $lockData = array(
            'pid' => getmypid(),
            'heartbeat' => date('Y-m-d H:i:s'),
            'updates' => $this->updates
        );
        file_put_contents($lockFilename, json_encode($lockData));

        return true;
    }

    protected function getLockFilename()
    {
        return '/tmp/pool.seo.worker.' . $this->token . '.lock';
    }
}
