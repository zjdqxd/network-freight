# 陕西省网络货运省厅接口对接laravel扩展包

[![Latest Stable Version](http://poser.pugx.org/zjdqxd/network-freight/v)](https://packagist.org/packages/zjdqxd/network-freight)
[![Total Downloads](http://poser.pugx.org/zjdqxd/network-freight/downloads)](https://packagist.org/packages/zjdqxd/network-freight)
[![Latest Unstable Version](http://poser.pugx.org/zjdqxd/network-freight/v/unstable)](https://packagist.org/packages/zjdqxd/network-freight)
[![License](http://poser.pugx.org/zjdqxd/network-freight/license)](https://packagist.org/packages/zjdqxd/network-freight)


# 介绍
shaanxi-network-freight 是陕西省网络货运监管平台的一个SDK，在官方只有JAVA的SDK，而PHP中的加密方式与JAVA不同导致上传不成功，为了解决此问题，自己写了个SDK，如有任何问题请发送邮件至2912484894@qq.com

# 要求
- php版本:>=7.2
- laravel版本: Laravel6+


# 安装

```php
composer require zjdqxd/shaanxi-network-freight
```

# 在非laravel项目中使用
```php
// 上报司机
app(NetworkFreightService::class)->reportDriver([
    具体字段请以陕西省监管平台的文档为准
]);
// 上报车辆
app(NetworkFreightService::class)->reportVehicle([
    具体字段请以陕西省监管平台的文档为准
]);
// 上报资金流水
app(NetworkFreightService::class)->reportCapitalFlow([
    具体字段请以陕西省监管平台的文档为准
]);
// 上报资金流水
app(NetworkFreightService::class)->reportTransport([
    具体字段请以陕西省监管平台的文档为准
]);
```

# 在laravel项目中使用

安装成功后执行
```php
php artisan vendor:publish --provider="ShaanXiNetworkFreight\NetworkFreightServiceProvider"

```
会自动将`networkFreight.php`添加到您项目的配置文件当中

# 相关配置

### 环境变量
默认环境变量为`production`
```php
NETWORK_FREIGHT_ENV=dev
```
