#!/bin/bash
# 前端库兼容层：为原版文件名建立指向升级后新库的软链接。
#
# 背景：内核已把 jQuery 升到 3.7.1、Bootstrap 升到 4.6.2 bundle（修一批公开 CVE），
# 原版的 jquery-3.1.0.js / bootstrap.js / popper.js 随之删除。但生态里为原版编写的
# 主题与插件（尤其带 overwrite/ 目录、整文件覆盖内核模板的那些）仍按原版文件名引用，
# 文件缺失时会被伪静态规则重写成 HTML 首页，浏览器把 HTML 当脚本解析，导致整页 JS 失效。
#
# 装了这类主题/插件后在站点根目录执行本脚本即可；只装内核不需要。
set -e

WEBROOT="${1:-$(cd "$(dirname "$0")/.." && pwd)}"
cd "$WEBROOT/view/js"

ln -sfn jquery-3.7.1.min.js jquery-3.1.0.js
ln -sfn jquery-3.7.1.min.js jquery-3.3.1.js
ln -sfn bootstrap-4.6.2.bundle.min.js bootstrap.js
ln -sfn bootstrap-4.6.2.bundle.min.js bootstrap.bundle.js

# Popper 已打包进 bootstrap bundle，这里只需满足模板的加载顺序
printf '/* Popper is bundled inside bootstrap-4.6.2.bundle.min.js */\n' > popper.js
printf '/* Popper is bundled inside bootstrap-4.6.2.bundle.min.js */\n' > popper-utils.js

OWNER=$(stat -c '%U:%G' ../../index.php 2>/dev/null || echo 'www-data:www-data')
chown -h "$OWNER" jquery-3.1.0.js jquery-3.3.1.js bootstrap.js bootstrap.bundle.js popper.js popper-utils.js 2>/dev/null || true

echo "兼容别名已建立："
ls -la jquery-3.1.0.js jquery-3.3.1.js bootstrap.js bootstrap.bundle.js popper.js popper-utils.js
