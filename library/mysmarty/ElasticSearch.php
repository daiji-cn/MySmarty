<?php

namespace library\mysmarty;

/**
 * 全文搜索
 *
 * @author 戴记
 *
 */
class ElasticSearch
{

    private static $protocol = CONFIG['database']['elasticsearch']['protocol'];

    private static $ip = CONFIG['database']['elasticsearch']['ip'];

    private static $port = CONFIG['database']['elasticsearch']['port'];

    // 数据库，索引
    private static $database = CONFIG['database']['elasticsearch']['database'];

    // 表，文档
    private static $table = CONFIG['database']['elasticsearch']['table'];

    // 表的自增主键 字段名
    private static $pk = 'id';

    // 适当的 HTTP方法,GET`、 `POST`、 `PUT`、 `HEAD 或者 `DELETE`
    private static $verb = 'GET';

    private static $obj = null;

    private static $mWhere = [];

    private static $mSize = 10;

    private static $mFrom = 0;

    private static $mTimeout = '3s';

    private static $mDatabase = false;

    private static $mTable = false;

    private static $mSort = [];

    private static $mField = '';

    private static $mProperties = [];

    private static $mJsonType = JSON_UNESCAPED_UNICODE;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 获取对象实例
     *
     * @return ElasticSearch
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 发送数据
     *
     * @param string $path
     *            请求路径
     * @param array $data
     *            请求数据,json格式
     * @return mixed
     */
    public function exec($path, $data = [])
    {
        $ch = curl_init($this->getUrl($path));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::$verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        if (!empty($data)) {
            if (is_array($data)) {
                $data = json_encode($data, self::$mJsonType);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($res, true);
        return $res;
    }

    /**
     * 获取完整的请求url
     *
     * @param string $path
     *            请求path
     * @return string
     */
    private function getUrl($path = '')
    {
        return self::$protocol . '://' . self::$ip . ':' . self::$port . $path;
    }

    /**
     * 修改请求方式
     *
     * @param string $verb
     *            get,post,put,...
     * @return ElasticSearch
     */
    public function setVerb($verb)
    {
        self::$verb = strtoupper($verb);
        return $this;
    }

    /**
     * 以GET方式执行
     *
     * @param string $path
     * @param array $data
     *            请求数据,json格式
     * @return mixed
     */
    public function get($path, $data = [])
    {
        return $this->setVerb('GET')->exec($path, $data);
    }

    /**
     * 以POST方式执行
     *
     * @param string $path
     * @param array $data
     *            请求数据,json格式
     * @return mixed
     */
    public function post($path, $data = [])
    {
        return $this->setVerb('POST')->exec($path, $data);
    }

    /**
     * 以PUT方式执行
     *
     * @param string $path
     * @param array $data
     *            请求数据,json格式
     * @return mixed
     */
    public function put($path, $data = [])
    {
        return $this->setVerb('PUT')->exec($path, $data);
    }

    /**
     * 以DELETE方式执行
     *
     * @param string $path
     * @param array $data
     *            请求数据,json格式
     * @return mixed
     */
    public function delete($path, $data = [])
    {
        return $this->setVerb('DELETE')->exec($path, $data);
    }

    /**
     * 以HEAD方式执行
     *
     * @param string $path
     * @param array $data
     *            请求数据,json格式
     * @return mixed
     */
    public function head($path, $data = [])
    {
        return $this->setVerb('HEAD')->exec($path, $data);
    }

    /**
     * 切换数据库，索引
     *
     * @param string $database
     *            数据库，索引
     * @return ElasticSearch
     */
    public function name($database)
    {
        self::$database = $database;
        return $this;
    }

    /**
     * 切换表，文档
     *
     * @param string $table
     *            表，文档
     * @return ElasticSearch
     */
    public function table($table)
    {
        self::$table = $table;
        return $this;
    }

    /**
     * 添加数据
     *
     * @param array $data
     * @return mixed 0 添加失败，1 添加成功，2 更新成功
     */
    public function insert($data)
    {
        $path = $this->generatePathById($this->getPkValue($data));
        if (substr($path, -1) === '/') {
            $result = $this->post($path, $data);
        } else {
            $result = $this->put($path, $data);
        }
        $status = 0;
        if (isset($result['result'])) {
            if ($result['result'] === 'created') {
                $status = 1;
            } else if ($result['result'] === 'updated') {
                $status = 2;
            }
        }
        return $status;
    }

    /**
     * 根据id生成path
     *
     * @param string|int $id
     *            主键id
     * @return string
     */
    private function generatePathById($id)
    {
        $path = '/' . self::$database . '/' . self::$table . '/';
        if (!empty($id)) {
            $path .= $id;
        }
        return $path;
    }

    /**
     * 生成搜索path
     *
     * @param string|bool $database
     * @param string|bool $table
     * @return string
     */
    private function generatePathBySearch($database = false, $table = false)
    {
        $path = $this->getPath($database, $table);
        $path .= '/_search?size=' . self::$mSize . '&from=' . self::$mFrom . '&timeout=' . self::$mTimeout;
        return $path;
    }

    /**
     * 生成验证path
     *
     * @param string|bool $database
     * @param string|bool $table
     * @return string
     */
    private function generatePathByValidate($database = false, $table = false)
    {
        $path = $this->getPath($database, $table);
        $path .= '/_validate/query';
        return $path;
    }

    /**
     * 生成清空表path
     *
     * @param string|bool $database
     * @param string|bool $table
     * @return string
     */
    private function generatePathByTruncate($database = false, $table = false)
    {
        $path = $this->getPath($database, $table);
        $path .= '/_delete_by_query?conflicts=proceed';
        return $path;
    }

    /**
     * 生成path路径
     *
     * @param string|bool $database
     * @param string|bool $table
     * @return string
     */
    private function getPath($database = false, $table = false)
    {
        if ($database === false) {
            $database = self::$database;
        }
        if ($table === false) {
            $table = self::$table;
        }
        $path = '';
        if (!empty($database)) {
            $path .= '/' . $database;
        }
        if (!empty($table)) {
            $path .= '/' . $table;
        }
        return $path;
    }

    /**
     * 返回数组中的主键值
     *
     * @param array $data
     * @return number
     */
    private function getPkValue($data)
    {
        $id = 0;
        $pk = self::$pk;
        if (isset($data[$pk])) {
            $id = $data[$pk];
        }
        return $id;
    }

    /**
     * 查找数据
     *
     * @return mixed|array
     */
    public function find()
    {
        $this->limit(0, 1);
        $data = $this->select();
        if (!empty($data)) {
            return $data[0];
        }
        return [];
    }

    /**
     * 重新初始化搜索条件
     */
    private function initSearch()
    {
        self::$mWhere = [];
        self::$mFrom = 0;
        self::$mSize = 10;
        self::$mTimeout = '3s';
        self::$mDatabase = false;
        self::$mTable = false;
        self::$mSort = [];
        self::$mField = '';
        self::$mProperties = [];
        self::$mJsonType = JSON_UNESCAPED_UNICODE;
    }

    /**
     * where条件
     *
     * @param
     *            $field
     * @param string $value
     * @param string $op
     * @param string $connector
     * @return $this
     */
    public function where($field, $value, $op = '=', $connector = 'and')
    {
        self::$mWhere[] = [
            $field,
            $value,
            $op,
            $connector
        ];
        return $this;
    }

    /**
     * 处理条件
     *
     * @return array
     */
    private function dealWhere()
    {
        $search = [];
        $must = []; // ==
        $must_not = []; // !=
        $should = []; // or
        $range = []; // > < >= <=
        $exists = []; // is not null
        $match = [];
        $isLike = true; // 是否匹配
        $match_phrase = [];
        $prefix = [];
        $wildcard = [];
        if (!empty(self::$mWhere)) {
            foreach (self::$mWhere as $v) {
                $term = [
                    'term' => [
                        $v[0] => $v[1]
                    ]
                ];
                switch ($v[2]) {
                    case '=':
                        if ($v[3] === 'and') {
                            $must[] = $term;
                        } else {
                            $must_not[] = $term;
                        }
                        break;
                    case '!=':
                        if ($v[1] === null) {
                            $exists['field'] = $v[0];
                        } else {
                            $must_not[] = $term;
                        }
                        break;
                    case '>':
                        $range[$v[0]]['gt'] = $v[1];
                        break;
                    case '>=':
                        $range[$v[0]]['gte'] = $v[1];
                        break;
                    case '<':
                        $range[$v[0]]['lt'] = $v[1];
                        break;
                    case '<=':
                        $range[$v[0]]['lte'] = $v[1];
                        break;
                    case 'match':
                        if ($v[3] === 'and') {
                            $isLike = true;
                        } else {
                            $isLike = false;
                        }
                        $match[] = [
                            'match' => [
                                $v[0] => $v[1]
                            ]
                        ];
                        break;
                    case 'match_phrase':
                        $match_phrase[$v[0]] = $v[1];
                        break;
                    case 'prefix':
                        $prefix[$v[0]] = $v[1];
                        break;
                    case 'wildcard':
                        $wildcard[$v[0]] = $v[1];
                        break;
                }
            }
        }
        $bool = [];
        if (!empty($must)) {
            $bool['must'] = $must;
        }
        if (!empty($must_not)) {
            $bool['must_not'] = $must_not;
        }
        if (!empty($should)) {
            $bool['should'] = $should;
        }
        if (!empty($must)) {
            $bool['must'] = $must;
        }
        if (!empty($match)) {
            if (count($match) === 1) {
                $match = $match[0];
            }
            if ($isLike) {
                $bool['must'] = $match;
            } else {
                $bool['must_not'] = $match;
            }
        }
        if (!empty($bool)) {
            $search['query']['bool'] = $bool;
        }
        if (!empty($range)) {
            $search['query']['constant_score']['filter']['range'] = $range;
        }
        if (!empty($exists)) {
            $search['query']['constant_score']['filter']['exists'] = $exists;
        }
        if (!empty(self::$mSort)) {
            $search['sort'] = self::$mSort;
        }
        if (!empty(self::$mField)) {
            $search['_source'] = explode(',', self::$mField);
        }
        if (!empty($match_phrase)) {
            $search['query']['match_phrase'] = $match_phrase;
        }
        if (!empty($prefix)) {
            $search['query']['prefix'] = $prefix;
        }
        if (!empty($wildcard)) {
            $search['query']['wildcard'] = $wildcard;
        }
        return $search;
    }

    /**
     *
     * 获取数据，去除掉无用数据
     *
     * @return array
     */
    public function select()
    {
        $result = $this->rawSelect();
        $data = [];
        if (isset($result['hits']['hits'])) {
            foreach ($result['hits']['hits'] as $v) {
                if (isset($v['_source'])) {
                    $data[] = $v['_source'];
                } else {
                    $data[] = $v['_id'];
                }
            }
        }
        return $data;
    }

    /**
     * 获取原生数据
     *
     * @return mixed
     */
    public function rawSelect()
    {
        $data = $this->dealWhere();
        $path = $this->generatePathBySearch(self::$mDatabase, self::$mTable);
        $this->initSearch();
        return $this->get($path, $data);
    }

    /**
     * 获取原生验证结果
     *
     * @return mixed
     */
    public function rawValidate()
    {
        $path = $this->generatePathByValidate(self::$mDatabase, self::$mTable);
        $data = $this->dealWhere();
        $this->initSearch();
        return $this->get($path, $data);
    }

    /**
     * 验证请求数据是否正确
     *
     * @return mixed
     */
    public function validate()
    {
        $result = $this->rawValidate();
        return $result['valid'];
    }

    /**
     * 限制条件
     *
     * @param int $from
     *            从第几条开始
     * @param int $size
     *            取多少条数据
     * @return $this
     */
    public function limit($from, $size = 10)
    {
        self::$mFrom = $from;
        self::$mSize = $size;
        return $this;
    }

    /**
     * 分页查询
     *
     * @param int $page
     *            第几页
     * @param int $size
     *            每页多少条数据
     * @return $this
     */
    public function page($page, $size = 10)
    {
        $from = ($page - 1) * $size;
        return $this->limit($from, $size);
    }

    /**
     * 设置请求超时时间
     *
     * @param int $timeout
     *            单位秒
     * @return $this
     */
    public function setTimeOut($timeout)
    {
        self::$mTimeout = ($timeout * 1000) . 'ms';
        return $this;
    }

    /**
     * 设置主键名称
     *
     * @param string $pk
     *            主键名称，如id
     * @return ElasticSearch
     */
    public function setPk($pk)
    {
        self::$pk = $pk;
        return $this;
    }

    /**
     * 设置搜索库
     *
     * @param string|bool $database
     *            索引，数据库名,多个以逗号分隔
     * @param string|bool $table
     *            类型，数据库表，多个以逗号分隔
     *            /_search
     *            在所有的索引中搜索所有的类型
     *            /gb/_search
     *            在 gb 索引中搜索所有的类型
     *            /gb,us/_search
     *            在 gb 和 us 索引中搜索所有的文档
     *            /g星号,u星号/_search
     *            在任何以 g 或者 u 开头的索引中搜索所有的类型
     *            /gb/user/_search
     *            在 gb 索引中搜索 user 类型
     *            /gb,us/user,tweet/_search
     *            在 gb 和 us 索引中搜索 user 和 tweet 类型
     *            /_all/user,tweet/_search
     *            在所有的索引中搜索 user 和 tweet 类型
     * @return $this
     */
    public function search($database = false, $table = false)
    {
        self::$mTable = $table;
        self::$mDatabase = $database;
        return $this;
    }

    /**
     * 排序
     *
     * @param string $field
     *            排序字段
     * @param string $order
     *            排序规则，desc 降序,asc 升序
     * @return $this
     */
    public function order($field, $order = 'desc')
    {
        self::$mSort[] = [
            $field => [
                'order' => $order
            ]
        ];
        return $this;
    }

    /**
     * 设置查询字段
     *
     * @param string $field
     *            多个字段逗号分隔
     * @return $this
     */
    public function field($field)
    {
        self::$mField = $field;
        return $this;
    }

    /**
     * 获取集群监控信息监控状态
     * 返回 status 字段：
     * green 所有的主分片和副本分片都正常运行
     * yellow 所有的主分片都正常运行，但不是所有的副本分片都正常运行
     * red 有主分片没能正常运行
     *
     * @return mixed
     */
    public function getHealth()
    {
        return $this->get('/_cluster/health');
    }

    /**
     * 创建索引
     *
     * @param int $shards
     *            主分片数目
     * @param int $replicas
     *            副本分片数目
     * @return bool
     */
    public function createDataBase($shards = 1, $replicas = 0)
    {
        $result = $this->put('/' . self::$database, [
            'settings' => [
                'number_of_shards' => $shards,
                'number_of_replicas' => $replicas
            ]
        ]);
        return !isset($result['error']);
    }

    /**
     * 设置数据库，索引
     *
     * @param string $database
     * @return $this
     */
    public function setDataBase($database)
    {
        self::$database = $database;
        return $this;
    }

    /**
     * 设置表，文档
     *
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        self::$table = $table;
        return $this;
    }

    /**
     * 更新副本分区数目
     *
     * @param int $replicas
     * @return bool
     */
    public function updateReplicas($replicas)
    {
        $result = $this->put('/' . self::$database . '/_settings', [
            'number_of_replicas' => $replicas
        ]);
        return !isset($result['error']);
    }

    /**
     * 检查主键id的文档是否存在
     *
     * @param int $id
     * @return bool
     */
    public function checkId($id)
    {
        $url = $this->getUrl($this->generatePathById($id));
        stream_context_set_default(array(
            'http' => array(
                'method' => 'HEAD'
            )
        ));
        $res = get_headers($url);
        if (false !== strpos($res[0], "200")) {
            return true;
        }
        return false;
    }

    /**
     * 更细文档
     *
     * @param array $data
     * @return bool
     */
    public function update($data)
    {
        $id = $this->getPkValueByWhere();
        if (empty($id)) {
            return false;
        }
        $path = $this->generatePathById($id) . '/_update';
        $result = $this->post($path, [
            'doc' => $data
        ]);
        return $result['result'] === 'updated';
    }

    /**
     * 删除数据
     *
     * @return bool
     */
    public function del()
    {
        $ids = $this->getPkValueByWhere();
        if (empty($ids)) {
            return false;
        }
        $deleteFlag = false;
        foreach ($ids as $id) {
            $result = $this->delete($this->generatePathById($id));
            if (isset($result['result'])) {
                $deleteFlag = $result['result'] === 'deleted';
            }
        }
        return $deleteFlag;
    }

    /**
     * 自增
     *
     * @param int $field
     *            自增字段
     * @param int $num
     *            自增值
     * @return bool
     */
    public function setInc($field, $num = 1)
    {
        $id = $this->getPkValueByWhere();
        if (empty($id)) {
            return false;
        }
        $path = $this->generatePathById($id) . '/_update';
        $result = $this->post($path, [
            'script' => 'ctx._source.' . $field . '+=' . $num
        ]);
        if (isset($result['result']) && $result['result'] === 'updated') {
            return true;
        }
        return false;
    }

    /**
     * 根据where条件找到主键id的值
     *
     * @return bool|array
     */
    private function getPkValueByWhere()
    {
        $data = $this->rawSelect();
        if (empty($data['hits']['hits'])) {
            return false;
        }
        $ids = [];
        foreach ($data['hits']['hits'] as $v) {
            $ids[] = $v['_id'];
        }
        return $ids;
    }

    /**
     * 自减
     *
     * @param int $field
     *            自减字段
     * @param int $num
     *            自减值
     * @return bool
     */
    public function setDec($field, $num = 1)
    {
        return $this->setInc($field, $num * (-1));
    }

    /**
     * 获取映射
     *
     * @return mixed
     */
    public function getMapping()
    {
        $path = '/' . self::$database . '/_mapping/' . self::$table;
        return $this->get($path);
    }

    /**
     * 分析器
     *
     * @param string $text
     *            文字
     * @param string $analyzer
     *            分词器
     * @return mixed
     */
    public function getAnalyze($text, $analyzer = 'standard')
    {
        $path = '/_analyze';
        $data = [
            'analyzer' => $analyzer,
            'text' => $text
        ];
        return $this->get($path, $data);
    }

    /**
     * 删除数据库，索引
     *
     * @return bool
     */
    public function dropDataBase()
    {
        $result = $this->delete('/' . self::$database);
        return !isset($result['error']);
    }

    /**
     * 创建数据库
     * @param bool $enabled
     * @return bool
     */
    public function createDataBaseByMapping($enabled = TRUE)
    {
        if (empty(self::$mProperties)) {
            return $this->createDataBase();
        }
        $path = '/' . self::$database;
        $data = [
            'mappings' => [
                self::$table => [
                    '_source' => [
                        'enabled' => $enabled
                    ],
                    'properties' => self::$mProperties
                ]
            ]
        ];
        $result = $this->put($path, $data);
        return !isset($result['error']);
    }

    /**
     * 生成property
     *
     * @param string $field
     *            字段
     * @param string $type
     *            类型，字符串: text,keyword，整数 :long, integer, short, byte, double, float, half_float, scaled_float，浮点数: float, double，布尔型: boolean，日期: date
     * @param string $analyzer
     *            分析器，standard，whitespace，simple，english
     * @param string $search_analyzer
     *            搜索分词器
     * @param string $index
     *            怎样索引字符串，analyzed 首先分析字符串，然后索引它。换句话说，以全文索引这个域。not_analyzed 索引这个域，所以它能够被搜索，但索引的是精确值。不会对它进行分析。no 不索引这个域。这个域不会被搜索到
     * @param string $format
     *            strict_date_optional_time，epoch_millis
     * @return $this
     */
    public function setProperty($field, $type = 'text', $analyzer = '', $search_analyzer = '', $index = '', $format = '')
    {
        $tmp = [
            'type' => $type
        ];
        if (!empty($analyzer)) {
            $tmp['analyzer'] = $analyzer;
        }
        if (!empty($index)) {
            $tmp['index'] = $index;
        }
        if (!empty($format)) {
            $tmp['format'] = $format;
        }
        if (!empty($search_analyzer)) {
            $tmp['search_analyzer'] = $search_analyzer;
        }
        self::$mProperties[$field] = $tmp;
        return $this;
    }

    /**
     * 改变json编码方式
     *
     * @param int $type
     *            默认JSON_UNESCAPED_UNICODE
     * @return $this
     */
    public function changeJsonType($type)
    {
        self::$mJsonType = $type;
        return $this;
    }

    /**
     * 单个词搜索
     *
     * @param string $field
     *            搜索字段
     * @param string|array $value
     *            数组或空格或逗号分隔的字符串
     * @return ElasticSearch
     */
    public function match($field, $value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        return $this->where($field, $value, 'match');
    }

    /**
     * 短语匹配
     *
     * @param string $field
     *            搜索字段
     * @param string|array $value
     *            数组或空格分隔的字符串
     * @return ElasticSearch
     */
    public function matchPhrase($field, $value)
    {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        return $this->where($field, $value, 'match_phrase');
    }

    /**
     * 前缀查询
     *
     * @param string $field
     * @param string $value
     * @return ElasticSearch
     */
    public function prefix($field, $value)
    {
        return $this->where($field, $value, 'prefix');
    }

    /**
     * 正则表达式查询
     *
     * @param string $field
     * @param string $value
     * @return ElasticSearch
     */
    public function wildcard($field, $value)
    {
        return $this->where($field, $value, 'wildcard');
    }

    /**
     * 设置json编码数据格式
     *
     * @param int $type
     * @return $this
     */
    public function setMJsonType($type)
    {
        self::$mJsonType = $type;
        return $this;
    }

    /**
     * 清空文档，表数据
     *
     * @param string|bool $database
     * @param string|bool $table
     * @return bool
     */
    public function truncate($database = FALSE, $table = FALSE)
    {
        $path = $this->generatePathByTruncate($database, $table);
        $data = [
            'query' => [
                'match_all' => []
            ]
        ];
        $result = $this->setMJsonType(JSON_FORCE_OBJECT)->post($path, $data);
        return isset($result['deleted']);
    }

    /**
     * 获取最后一个文档的id
     * @return int
     */
    public function getLastInsID()
    {
        $result = $this->order('id', 'desc')->find();
        if (!empty($result) && isset($result['id'])) {
            return $result['id'];
        }
        return 0;
    }

    /**
     * 统计文档的总个数
     * @return int
     */
    public function count()
    {
        $result = $this->rawSelect();
        if (!empty($result) && isset($result['hits']['total'])) {
            return $result['hits']['total'];
        }
        return 0;
    }

    /**
     * 精确查询
     * @param string $field
     * @param mixed $value
     * @return ElasticSearch
     */
    public function term($field, $value)
    {
        return $this->where($field, $value, '=');
    }
}