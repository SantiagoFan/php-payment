<?php

namespace JoinPhpCommon\utils;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
/**
 * Class JwtAuth
 * @package JoinPhpCommon\utils
 */
class JwtAuth
{
    private $token_id = 'join_token';//id防止重复
    private $secret ="";//加密钥匙
    private $audience ="";//接收者
    private $issuer ="";//颁发者
    function __construct($token_id,$secret,$audience,$issuer)
    {
        $this->token_id= $token_id;
        $this->secret =$secret;
        $this->audience =$audience;
        $this->issuer =$issuer;
    }
    /**
     * 配置秘钥加密
     * @return Configuration
     */
    public function getConfig()
    {
        $configuration = Configuration::forSymmetricSigner(
        // You may use any HMAC variations (256, 384, and 512)
            new Sha256(),
            // replace the value below with a key of your own!
            InMemory::base64Encoded($this->secret)
        // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );
        return $configuration;
    }
    /**
     * 通过用户信息创建 token 串 并存储到cookie 中
     **/
    public function CreateToken($data = [])
    {
        $config = $this->getConfig();
        assert($config instanceof Configuration);
        $now = new DateTimeImmutable();
        $builder = $config->builder()
            // 签发人
            ->issuedBy($this->issuer)
            // 受众
            ->permittedFor($this->audience)
            // JWT ID 编号 唯一标识
            ->identifiedBy($this->token_id)
            // 签发时间
            ->issuedAt($now)
            // 在1分钟后才可使用
//            ->canOnlyBeUsedAfter($now->modify('+1 minute'))
            // 过期时间1小时
            ->expiresAt($now->modify('+1 hour'));
        // 自定义参数
        foreach ($data as $k=>$v){
            $builder->withClaim($k, $v);//自定义数据
        }
        // 生成token
        $token = $builder->getToken($config->signer(), $config->signingKey());
        return (String) $token;
    }
    /**
     * 检测Token是否过期与篡改
     * @param token
     * @return boolean
     **/
    public function validateToken($token = null)
    {
        $config = $this->getConfig();
        assert($config instanceof Configuration);

        //验证jwt id是否匹配
        $validate_jwt_id = new \Lcobucci\JWT\Validation\Constraint\IdentifiedBy($this->token_id);
        $config->setValidationConstraints($validate_jwt_id);
        //验证签发人url是否正确
        $validate_issued = new \Lcobucci\JWT\Validation\Constraint\IssuedBy('http://example.com');
        $config->setValidationConstraints($validate_issued);
        //验证客户端url是否匹配
        $validate_aud = new \Lcobucci\JWT\Validation\Constraint\PermittedFor('http://example.org');
        $config->setValidationConstraints($validate_aud);

        //验证是否过期
        $timezone = new \DateTimeZone('Asia/Shanghai');
        $now = new \Lcobucci\Clock\SystemClock($timezone);
        $validate_jwt_at = new \Lcobucci\JWT\Validation\Constraint\ValidAt($now);
        $config->setValidationConstraints($validate_jwt_at);

        $constraints = $config->validationConstraints();
        $token = $config->parser()->parse($token);
        try {
            $config->validator()->assert($token, ...$constraints);
            return true;
        } catch (RequiredConstraintsViolated $e) {
            // list of constraints violation exceptions:
            var_dump($e->violations());
            return false;
        }
    }

    /**
     * 解析token
     * @param string $token
     * @return array|string Claims data
     */
    public function parseToken(string $token,$filter =true,$filter_array=['iss','aud','jti','iat','exp']){
        $config = self::getConfig();
        assert($config instanceof Configuration);
        $token = $config->parser()->parse($token);
        $array = $token->claims()->all();
        if($filter){
            $data = array_filter($array,function ($var) use ($filter_array){
                return !in_array($var,$filter_array);
            },ARRAY_FILTER_USE_KEY);
            return $data;
        }
        else{
            return $array;
        }
    }
}