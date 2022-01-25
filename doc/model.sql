--sql 脚本创建 聚合支付记录表
create table if not exists pay_order
(
    id varchar(64) not null comment '支付记录表',
    title varchar(128) null comment '支付项目名称',
    is_refund tinyint(1) default 0 null comment '是否为退款',
    state int default 0 null comment '支付完成状态 -1撤销 0 默认 1 交易中 2支付完成',
    business_name varchar(50) null comment '业务类别：1.商品支付',
    business_no varchar(128) null comment '内部业务 关联订单号码',
    pay_channel varchar(50) null comment '支付渠道：alipay,wxpay',
    pay_channel_no varchar(128) null comment '支付渠道 返回的外部订单号',
    amount decimal(10,2) null comment '操作金额 支付为正 退款为负',
    real_amount decimal(10,2) null,
    apply_time datetime null comment '下单时间',
    complete_time datetime null comment '交易完成时间',
    original_amount decimal(10,2) null comment '原订单交易金额',
    original_id varchar(64) null comment '原始交易订单',
    constraint pay_order_order_code_uindex
    unique (id)
    );

alter table testdb.pay_order
    add primary key (id);

