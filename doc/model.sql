--sql 脚本创建 聚合支付记录表
create table pay_order
(
    id          varchar(64)    not null comment '支付记录表',
    title       varchar(128)   null comment '支付项目名称',
    channel_no  varchar(128)   null comment '支付渠道 返回的外部订单号',
    internal_no int            null comment '内部业务 关联订单号码',
    pay_channel varchar(50)    null comment '支付渠道：alipay,wxpay',
    business_type    varchar(50)         null comment '业务类别：1.商品支付 等',
    amount      decimal(10, 2) null comment '待支付金额',
    pay_amount  decimal(10, 2) null comment '实际支付金额',
    pay_apply_time    datetime       null comment '下单时间',
    pay_time    datetime       null comment '支付完成时间',
    state       int default 0  null comment '支付完成状态 0 未完成 1支付完成',
    constraint pay_order_order_code_uindex
        unique (id)
)
    comment '聚合支付流水表';

alter table pay_order
    add primary key (id);
