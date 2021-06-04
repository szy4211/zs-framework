<?php
/**
 * @description: mongodb操作类
 *
 * @date 2019-06-15
 * @author zornshuai@foxmail.com
 */

namespace Framework;


use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class MongoDb
{
    /**
     * @var Manager
     */
    protected $mongodb;
    /**
     * @var BulkWrite
     */
    protected $bulk;
    /**
     * @var WriteConcern
     */
    protected $writeConcern;
    /**
     * @var string 数据库名称
     */
    protected $db;
    /**
     * @var string 表名
     */
    protected $table;
    /**
     * @var array 默认配置
     */
    protected $config = [
        'hostname' => 'localhost',
        'port'     => '27017',
        'username' => '',
        'password' => '',
        'database' => 'test',
    ];
    /**
     * @var array 过滤条件
     */
    protected $wheres = [];
    /**
     * @var array 查询参数
     */
    protected $options = [];

    //  排序
    const ASC = 1;
    const DESC = -1;

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config($config);
        }

        $this->connect();
        $this->writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
    }

    /**
     * @description: 获取mongodb对象
     *
     * @return Manager
     * @date 2019-06-15
     */
    public function mongodb()
    {
        return $this->mongodb;
    }

    /**
     * @description: 设置表名
     *
     * @param $table
     * @return MongoDb
     * @date 2019-06-15
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @description: 获取/设置配置
     *
     * @param array $config
     * @return mixed
     * @date 2019-06-15
     */
    public function config(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }

        return $this->config;
    }

    /**
     * =========================================
     * CURD操作 Start
     */

    /**
     * @description: 设置条件
     *
     * @param string|array $where
     * @param string|null $value
     * @return MongoDb
     * @date 2019-06-15
     */
    public function where($where, $value = null)
    {
        if (is_string($where)) {
            $where = [$where => $value];
        }

        if (isset($where['_id'])) {
            $where['_id'] = new ObjectId($where['_id']);
        }

        $this->wheres = array_merge($this->wheres, $where);

        return $this;
    }

    /**
     * @description: 设置or条件
     *
     * @param array $where
     * @return MongoDb
     * @date 2019-06-15
     */
    public function whereOr(array $where)
    {
        return $this->where('$or', $where);
    }

    /**
     * @description: 正则条件
     *
     * @param $key
     * @param $value
     * @return MongoDb
     * @date 2019-06-15
     */
    public function whereRegx($key, $value)
    {
        return $this->where($key, new Regex($value));
    }

    /**
     * @description: 排序
     *
     * @param array|string $column
     * @param int $orderType
     * @return MongoDb
     * @date 2019-06-15
     */
    public function order($column, $orderType = self::ASC)
    {
        $orderArray = [];
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                if (self::ASC != $value) {
                    $value = self::DESC;
                }
                $orderArray[$key] = $value;
            }
        }

        if (is_string($column)) {
            if (self::ASC != $orderType) {
                $orderType = self::DESC;
            }

            $orderArray = [$column => $orderType];
        }


        if (!empty($orderArray)) {
            if (isset($this->options['sort'])) {
                $this->options['sort'] = array_merge($this->options['sort'], $orderArray);
            } else {
                $this->options['sort'] = $orderArray;
            }
        }

        return $this;
    }

    /**
     * @description: 限制
     *
     * @param $num
     * @return MongoDb
     * @date 2019-06-15
     */
    public function limit($num)
    {
        $this->options['limit'] = $num;

        return $this;
    }

    /**
     * @description: 偏移
     *
     * @param $num
     * @return MongoDb
     * @date 2019-06-15
     */
    public function offset($num)
    {
        $this->options['skip'] = $num;

        return $this;
    }

    /**
     * @description: 查询指定字段
     *
     * @param array $fields
     * @return MongoDb
     * @date 2019-06-15
     */
    public function field(array $fields)
    {
        $projections =  $this->options['projection'] ?? [];
        foreach ($fields as $field) {
            $projections[$field] = 1;
        }

        if (!array_key_exists('_id', $projections)) {
            $projections['_id'] = 0;
        }

        $this->options['projection'] = $projections;

        return $this;
    }

    /**
     * @description: 统计
     *
     * @return int
     * @throws \MongoDB\Driver\Exception\Exception
     * @date 2019-06-15
     */
    public function count()
    {
        $result = $this->mongodb
            ->executeCommand($this->db, new Command([
                'count' => $this->table,
                'query' => $this->wheres
            ]))
            ->toArray();
        $count  = 0;
        if ($result) {
            $count = $result[0]->n;
        }

        return $count;
    }

    /**
     * @description: 查询一条数据
     *
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     * @date 2019-06-15
     */
    public function find()
    {
        $this->limit(1);

        $result = $this->select();
        if (!empty($result)) {
            $result = $result[0];
        }

        return $result;
    }

    /**
     * @description: 查询所有
     *
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     * @date 2019-06-15
     */
    public function select()
    {
        $cursor = $this->buildQuery();

        $result = [];
        foreach ($cursor as $document) {
            $result[] = json_decode(json_encode($document), true);
        }

        return $result;
    }

    /**
     * @description: 插入数据
     *
     * @param array $data
     * @return int|null 返回影响行数
     * @date 2019-06-15
     */
    public function insert(array $data)
    {
        $this->bulk = new BulkWrite();
        if (is_array(current($data))) {
            //  批量插入
            foreach ($data as $datum) {
                $this->bulk->insert($datum);
            }
        } else {
            $this->bulk->insert($data);
        }

        return $this->buildWrite()->getInsertedCount();
    }

    /**
     * @description: 更新数据
     *
     * @param array $data
     * @param bool $upsert 是否开启"插入更新"
     * @return int|null 返回影响行数
     * @date 2019-06-15
     */
    public function update($data = [], $upsert = false)
    {
        $this->bulk = new BulkWrite();

        $this->bulk->update($this->wheres, ['$set' => $data], ['multi' => true, 'upsert' => $upsert]);

        return $this->buildWrite()->getModifiedCount();
    }

    /**
     * @description: 删除操作
     *
     * @param bool $allowAll 是否允许无条件删除全部
     * @return int|null 返回影响行数
     * @date 2019-06-15
     */
    public function delete($allowAll = false)
    {
        //  防止误操作删除所有
        if (!$allowAll && empty($this->wheres)) {
            throw new BulkWriteException('Do not delete all ');
        }

        $this->bulk = new BulkWrite();
        $this->bulk->delete($this->wheres);

        return $this->buildWrite()->getDeletedCount();
    }

    /**
     * =========================================
     * CURD操作 End
     */

    /**
     * @description: 建立连接
     *
     * @date 2019-06-15
     */
    protected function connect()
    {
        $this->mongodb = new Manager($this->buildUri());
        $this->db      = $this->config['database'];
    }

    /**
     * @description: 构造查询条件
     *
     * @return \MongoDB\Driver\Cursor
     * @throws \MongoDB\Driver\Exception\Exception
     * @date 2019-06-15
     */
    protected function buildQuery()
    {
        return $this->mongodb
            ->executeQuery($this->db . '.' . $this->table,
                new Query($this->wheres, $this->options));
    }

    /**
     * @description: 构造写入条件
     *
     * @return \MongoDB\Driver\WriteResult
     * @date 2019-06-15
     */
    protected function buildWrite()
    {
        return $this->mongodb
            ->executeBulkWrite($this->db . '.' . $this->table,
                $this->bulk,
                $this->writeConcern);
    }

    /**
     * @description: 构造连接URI
     *
     * @return string
     * @date 2019-06-15
     */
    protected function buildUri()
    {
        $config = $this->config;

        $uri = "mongodb://";
        if (!empty($config['username'])) {
            $uri .= $config['username'] . ':' . $config['password'] . '@';
        }
        $uri .= $config['hostname'];
        if ($config['port']) {
            $uri .= ':' . $config['port'];
        }
        $uri .= '/' . $config['database'];

        return $uri;
    }
}