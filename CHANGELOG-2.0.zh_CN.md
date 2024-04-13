# v2.0 - TBD

# v2.0.0-RC 

- [#53](https://github.com/mineadmin/components/pull/53) 拆分组件 http-server
- [#55](https://github.com/mineadmin/components/pull/55) 拆分优化组件 crontab
- [#56](https://github.com/mineadmin/components/pull/56) 拆分`模块化`组件
- [#58](https://github.com/mineadmin/components/pull/58) 新增基础的 安全认证组件
- [#59](https://github.com/mineadmin/components/pull/59) 添加安全访问控制组件
- [#60](https://github.com/mineadmin/components/pull/60) 修复 MineCrontabProcess 在没有 .env 的情况下不运行(去掉毫无意义的判断)
- [#61](https://github.com/mineadmin/components/pull/61) 为导出 path 属性提供关联访问方式
- [#63](https://github.com/mineadmin/components/pull/63) 修复某些单词（category）复数情况下无法生成文件
- [#64](https://github.com/mineadmin/components/pull/64) 修复表名为前缀_表名(s)这种复数形式的情况下无法生成model
- [#65](https://github.com/mineadmin/components/pull/65) 精简优化 `mine:install` 命令，往后手动设置 .env
- [#66](https://github.com/mineadmin/components/pull/66) 删除内置的 amqp 处理