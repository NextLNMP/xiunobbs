# Xiuno BBS 5.0 · 修罗轻论坛社区延续版

> 从老黄 2020 年留下的最后遗作 4.0.4 出发，把国产轻论坛的巅峰之作，迭代到 AI 时代。

[English version below.](#english)

## 这是什么

Xiuno BBS（修罗轻论坛）是 2016 年诞生的国产轻论坛，以极致轻量著称：95 个 PHP 文件、约 2.3 万行代码、17 张数据表、9 个路由文件，单次请求 0.01 秒级，作者称它为"一辆纯手工打造的法拉利"。

2020 年 7 月 6 日，作者老黄关闭官网 bbs.xiuno.com，只留下一句"国内什么时候有真正的开源环境了再见!"。代码仓库、插件市场与配套电子书随之消失，此后六年再无真正的继任者。

Xiuno BBS 4.0 以 MIT 协议发布，允许自由修改、派生与商用。本仓库是它的社区延续版：先原样存档，再修复到现代 PHP，最后把它带进 AI 时代。

## 仓库结构

- `v4.0.4` 标签：官方最终版原始基线，原封不动，用于存档与考古
- `main` 分支：社区延续版主线
- `v5.0-dev` 分支：5.0 开发线
- `README-original.md`：老黄的原版说明，完整保留

## 5.0 路线图

### Phase 1 · 经典复活（v5.0-alpha）✅ 已完成

- PHP 8 全兼容（8.3 实测全链路，8.4 静态清零），mysql_* 驱动整体移植 mysqli
- 数据库字符集升级 utf8mb4，原生支持 emoji
- 安全审计，`eval` 使用点逐一复查
- 原味 UI 保留，17 张表结构不动，存量老站无损升级

### Phase 2 · 时代层（v5.0）

全部以插件实现，零内核侵入：

- **MCP 接口**：AI Agent 可读帖、发帖、管版，论坛成为人与 AI 共同的社区
- **I-Lang 输出层**：每个帖子对 AI 原生可读（[ilang.ai](https://ilang.ai)）
- **AI 审核插件**：内容合规自动化
- **SQLite 模式**：激活内核自带的 `db_pdo_sqlite` 驱动，单机零依赖建站

## 设计铁律

内核永远保持修罗哲学：轻、快、无赘肉。新能力一律插件化，插件机制本身就是修罗的魂。

## 协议与致谢

MIT 协议延续，老黄的原始版权信息完整保留于 `LICENSE.txt`，社区延续部分同样以 MIT 发布。

致敬老黄。一辆纯手工打造的法拉利，不应该锈在车库里。

## 相关生态

- [NextLNMP](https://nextlnmp.cn)：面向站长的一键 LNMP 环境
- [I-Lang](https://ilang.ai)：AI 时代的通信协议

---

## English

**Xiuno BBS** is a legendary ultra-light Chinese PHP forum born in 2016: 95 PHP files, ~23k lines of code, 17 database tables, 9 route files, 0.01s per request. The author called it "a handcrafted Ferrari".

In July 2020 the author shut everything down: the official site, the repositories, the plugin market and the ebooks. Released under the MIT license, Xiuno BBS allows free modification, derivation and commercial use. This repository is the community continuation.

**Repository layout**: the `v4.0.4` tag is the untouched final official release, archived for the record; `main` is the continuation line; `v5.0-dev` is where 5.0 happens; the original README is preserved as `README-original.md`.

**Roadmap**:

- **Phase 1, Classic Revival (v5.0-alpha)** ✅ shipped: full PHP 8.4+ compatibility, utf8mb4 with emoji support, security audit of every `eval` call, original UI preserved, schema untouched so existing sites upgrade losslessly.
- **Phase 2, The AI Era Layer (v5.0)**, implemented purely as plugins with zero core intrusion: an **MCP interface** so AI agents can read, post and moderate; an **I-Lang output layer** making every thread natively machine-readable ([ilang.ai](https://ilang.ai)); an **AI moderation plugin**; and a **SQLite mode** activating the built-in `db_pdo_sqlite` driver for zero-dependency single-box deployment.

**Design law**: the core stays true to the Xiuno philosophy, light, fast, no fat. Everything new ships as a plugin, because the plugin system is the soul of Xiuno.

MIT licensed. The original copyright notice is fully preserved in `LICENSE.txt`. In memory of the original author: a handcrafted Ferrari should not rust in the garage.
