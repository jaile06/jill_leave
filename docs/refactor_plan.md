# `jill_leave` 模組完整重構規劃書

> 版本：v1.0 ｜ 建立日期：2026-07-28
> 目標：整合《教師請假所遺課程處理報告單》（紙本表單）與《GAS 校園調代課公告系統》之需求，
> 在 **不破壞既有功能、向後相容、PHP 8 可用** 的前提下，漸進式擴充 `jill_leave` 模組。

---

## 0. 前言與三大輸入

本規劃綜合以下三個輸入：

| 編號 | 輸入來源 | 角色 |
|------|----------|------|
| ① | 現有 `jill_leave` 模組程式碼 | 重構基礎（不可破壞） |
| ② | 紙本《臺南市立南新國中教師請假所遺課程處理報告單》 | 業務需求規格（欄位/流程/規則） |
| ③ | GAS 校園調代課公告系統（網頁） | 數位化參考（資料結構/UI） |

---

## 1. 現況盤點（已實作功能）

模組已相當成熟，採用 **TadTools / dinfo 模式**、**PHP 8**、**PSR-4 命名空間**（`XoopsModules\Jill_leave\`）。

### 1.1 已實作功能清單

| 功能 | 對應檔案 | 狀態 |
|------|----------|------|
| 請假 CRUD（新增/編輯/刪除/列表/分頁） | `class/Jill_leave.php` | ✅ 完成 |
| 假別分類管理（CRUD/拖曳排序/啟停） | `class/Jill_leave_cate.php` | ✅ 完成 |
| 代課日期 + 節次明細（批次儲存） | `class/Jill_leave_substitute.php` / `Jill_leave_class.php` | ✅ 完成 |
| 管理者代課總覽（依月份篩選） | `Jill_leave_substitute::overview()` | ✅ 完成 |
| Excel 鐘點費清冊匯出（xlsx） | `Jill_leave_substitute::export_excel()` | ✅ 完成 |
| 審核狀態（0待審/1通過/2駁回） | `Jill_leave::update_status()` | ✅ 完成 |
| 日期區間重疊檢查 | `Jill_leave::store()/update()` | ✅ 完成 |
| 請假公告區塊 | `blocks/jill_leave_show.php` + `announcement()` | ✅ 完成 |
| 權限（管理員/本人/Email 白名單） | `class/Tools.php` | ✅ 完成 |
| 雙層過濾 + CSRF Token + SweetAlert | 各 class | ✅ 完成 |

### 1.2 現有資料庫（4 張表）

| 資料表 | 用途 | 關鍵欄位 |
|--------|------|----------|
| `jill_leave` | 請假主表 | sn, uid, leavers, cate_sn, is_advisor, grade_class, **start_date(date)**, **end_date(date)**, status, create_date, update_date |
| `jill_leave_cate` | 假別分類 | cate_sn, cate_title, cate_sort, enable |
| `jill_leave_substitute` | 代課日期 | substitute_sn, sn, substitute_date, pay(self/school), type(daily/hour) |
| `jill_leave_class` | 代課節次明細 | class_sn, substitute_sn, sn, class_period, **subject(JSON: grade_class+subject)**, substitute_teacher |

> 📌 現有模型主軸：「請假日期 → 當天節次 → 代課老師」，**僅對應紙本表單『方式 1：委託代課』**。

---

## 2. 需求來源規格

### 2.1 紙本表單《教師請假所遺課程處理報告單》

**一、基本資料**：職稱、姓名(教師代碼)、職務代理人、公(差)假公文字號、假別、
請假日數(起訖年月日星期「時」至「時」、計 N 日)、事由。

**二、遺課處理方式（三擇一）**：

| 方式 | 說明 | 需記錄欄位 |
|------|------|-----------|
| **1. 委託他人代課** | ☐ 由教學組課務排代 ☐ 由教學組調課處理 | 月/日/星期/節次/科目/班級/代課教師 |
| **2. 自己補課** | 另行補課 | 補課 月/日/星期/節次 |
| **3. 與他人調課** | 雙方對調 | 調課 月/日/星期/節次 + 對調科目 + 對調教師 |

**三、簽章欄位（三層核決）**：請假教師（含切結）→ 教學組長 → 教務主任。

**業務規則（備註 5 點）**：
1. 請假須將所遺課程處理完畢。
2. 調課/代課/補課須先經教務處同意。
3. 代課教師須遴選相同科目之教師。
4. 調課須經對方同意。
5. 調課、補課須通知班級（教室日誌記載）。

### 2.2 GAS 校園調代課公告系統

每筆紀錄結構：

```
代課教師 / 類型(代課|調課) / 班級 / 原課程(日期+節次+科目) / 異動後(日期+節次+科目) / 請假教師
```

> 📌 GAS 的「原課程 vs 異動後」概念，正是紙本表單『方式 3 調課』與『方式 2 補課』的數位化。

---

## 3. 差距分析（Gap Analysis）⭐ 核心

### 3.1 基本資料欄位差距

| 紙本欄位 | 現有對應 | 差距 | 處理方式 |
|----------|----------|------|----------|
| 職稱 | ❌ 無 | 缺 | 主表新增 `job_title` |
| 姓名 | ✅ leavers | — | 沿用 |
| 教師代碼 | ❌ 無 | 缺 | 主表新增 `teacher_code` |
| 職務代理人 | ❌ 無 | 缺（註：與「代課教師」不同） | 主表新增 `duty_agent` |
| 公(差)假公文字號 | ❌ 無 | 缺 | 主表新增 `official_doc_no` |
| 假別 | ✅ cate_sn | — | 沿用 |
| 請假日數-起訖「時」 | ⚠️ 僅 date | 缺「時」 | 新增 `start_time`/`end_time` |
| 請假日數-計 N 日 | ❌ 無 | 缺 | 新增 `leave_days` |
| 事由 | ❌ 無 | 缺 | 主表新增 `reason` |

### 3.2 遺課處理方式差距

| 紙本方式 | 現有對應 | 差距 |
|----------|----------|------|
| 1. 委託代課 | ✅ jill_leave_class.substitute_teacher | 缺「☐課務排代/☐調課處理」勾選 → 新增 `handle_by` |
| 2. 自己補課 | ❌ 無 | 缺「補課日期/節次」 → 新增 `changed_date`/`changed_period` |
| 3. 與他人調課 | ❌ 無 | 缺「對調科目/對調教師/異動後日期節次」 → 新增 `swap_subject`/`swap_teacher` |
| 處理方式區分 | ❌ 無 | 缺 `handle_type`(substitute/makeup/swap) |

### 3.3 審核流程差距

| 紙本流程 | 現有對應 | 差距 |
|----------|----------|------|
| 請假教師簽章 | ⚠️ 僅 uid 記錄 | 缺簽章時間 |
| 教學組長核章 | ❌ 無 | 缺 |
| 教務主任核章 | ❌ 無 | 缺 |
| 三層簽章歷程 | ⚠️ 僅單一 status | 建議新增獨立簽章表 `jill_leave_approve` |

### 3.4 調課公告差距（GAS 整合）

| GAS 功能 | 現有對應 | 差距 |
|----------|----------|------|
| 全校調代課公告列表 | ⚠️ 僅管理者總覽 + 簡易區塊 | 缺公開公告頁/卡片式 UI |
| 類型(代課/調課)篩選 | ❌ 無 | 需 `handle_type` 支援 |
| 原課程 vs 異動後對照 | ❌ 無 | 需新欄位支援 |

---

## 4. 重構總原則

1. **漸進式重構**：分階段交付，每階段可獨立驗收、可獨立回滾。
2. **向後相容**：資料庫**只新增欄位/新增表**，不修改、不刪除既有欄位；舊資料預設值自動填補。
3. **不破壞既有邏輯**：既有 `store()/update()/get_all()` 等方法保留，以「擴充」而非「改寫」方式進行。
4. **PHP 8 相容**：延續 `match`、`??`、`?->`、嚴格型別；新增 SQL 避免保留字（如 `class`→`class_period`）。
5. **遵循現有鐵則**：入口檔不寫 SQL、Class 不 echo HTML、寫入型 op 加 `xoops_security_check()` + `token_form()`、刪除用 SweetAlert、硬編碼中文進語言包。
6. **MyISAM 無 Transaction**：跨表寫入延續既有「PHP 端把關 + 失敗手動 DELETE」模式。

---

## 5. 資料庫擴充方案（向後相容）

### 5.1 擴充 `jill_leave` 主表（新增欄位）

```sql
ALTER TABLE `jill_leave`
  ADD COLUMN `job_title`       varchar(50)  NOT NULL DEFAULT '' COMMENT '職稱' AFTER `leavers`,
  ADD COLUMN `teacher_code`    varchar(50)  NOT NULL DEFAULT '' COMMENT '教師代碼' AFTER `job_title`,
  ADD COLUMN `duty_agent`      varchar(50)  NOT NULL DEFAULT '' COMMENT '職務代理人' AFTER `teacher_code`,
  ADD COLUMN `official_doc_no` varchar(100) NOT NULL DEFAULT '' COMMENT '公(差)假公文字號' AFTER `duty_agent`,
  ADD COLUMN `reason`          text         NULL COMMENT '事由' AFTER `official_doc_no`,
  ADD COLUMN `start_time`      varchar(10)  NOT NULL DEFAULT '' COMMENT '起始時間(時:分或節次)' AFTER `start_date`,
  ADD COLUMN `end_time`        varchar(10)  NOT NULL DEFAULT '' COMMENT '結束時間(時:分或節次)' AFTER `end_date`,
  ADD COLUMN `leave_days`      decimal(5,1) NOT NULL DEFAULT 1.0 COMMENT '請假日數(計N日)' AFTER `end_time`;
```

### 5.2 擴充 `jill_leave_class` 節次明細表（新增欄位，承載三種處理方式）

```sql
ALTER TABLE `jill_leave_class`
  ADD COLUMN `handle_type`   enum('substitute','makeup','swap') NOT NULL DEFAULT 'substitute' COMMENT '處理方式(代課/補課/調課)' AFTER `sn`,
  ADD COLUMN `handle_by`     enum('self','teaching_group') NOT NULL DEFAULT 'self' COMMENT '委託方式(自找/教學組)' AFTER `handle_type`,
  ADD COLUMN `original_date` date NULL COMMENT '原課程日期' AFTER `handle_by`,
  ADD COLUMN `changed_date`  date NULL COMMENT '異動後日期(補課/調課)' AFTER `class_period`,
  ADD COLUMN `changed_period` varchar(20) NOT NULL DEFAULT '' COMMENT '異動後節次' AFTER `changed_date`,
  ADD COLUMN `swap_subject`  varchar(100) NOT NULL DEFAULT '' COMMENT '對調科目(調課)' AFTER `substitute_teacher`,
  ADD COLUMN `swap_teacher`  varchar(50)  NOT NULL DEFAULT '' COMMENT '對調教師(調課)' AFTER `swap_subject`,
  ADD KEY `idx_handle_type` (`handle_type`);
```

> 📌 既有資料 `handle_type` 預設為 `'substitute'`，自動歸類為「委託代課」，**舊資料無需遷移即相容**。
> 📌 `original_date` 若為空，顯示時 fallback 至 `jill_leave_substitute.substitute_date`。

### 5.3 新增 `jill_leave_approve` 簽章歷程表（三層核決）

```sql
CREATE TABLE `jill_leave_approve` (
  `approve_sn` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '簽章編號',
  `sn` mediumint(8) unsigned NOT NULL COMMENT '關聯請假編號',
  `step` tinyint(1) unsigned NOT NULL COMMENT '簽章層級(1教師 2教學組長 3教務主任)',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT '簽章者uid',
  `signer_name` varchar(50) NOT NULL DEFAULT '' COMMENT '簽章者姓名',
  `result` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT '結果',
  `comment` varchar(255) NOT NULL DEFAULT '' COMMENT '簽注意見',
  `sign_time` datetime NULL COMMENT '簽章時間',
  PRIMARY KEY (`approve_sn`),
  KEY `idx_sn` (`sn`),
  KEY `idx_step` (`step`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='請假簽章歷程表';
```

> 📌 現有 `jill_leave.status` 保留作為「最終狀態」快速欄位；`jill_leave_approve` 記錄完整歷程，兩者並行不衝突。

### 5.4 版本升級

- `xoops_version.php`：`version` 由 `1.0.1` → `1.1.0`，`release_date` 更新。
- `include/onUpdate.php`：加入上述 `ALTER TABLE` / `CREATE TABLE` 的升級邏輯（先檢查欄位是否存在再執行，避免重複升級錯誤）。

---

## 6. 階段規劃（Roadmap）

> 每階段獨立可驗收、可回滾。建議依序執行，亦可視優先級調整。

### Phase 0｜現況健檢與基準（✅ 已完成）
- 讀取並盤點現有程式、資料庫、架構書。
- 產出本規劃書。

### Phase 1｜資料庫擴充與升級機制
- **任務**：實作 5.1 / 5.2 / 5.3 之 SQL，寫入 `sql/mysql.sql`（全新安裝用）與 `include/onUpdate.php`（升級用）。
- **改動**：`sql/mysql.sql`、`include/onUpdate.php`、`xoops_version.php`(版本號)。
- **驗收**：全新安裝與舊版升級皆成功；既有假單資料正常顯示，`handle_type` 自動為 `substitute`。
- **風險**：升級 SQL 重複執行 → 加「欄位存在檢查」。

### Phase 2｜基本資料欄位擴充
- **任務**：表單/儲存/顯示加入 職稱、教師代碼、職務代理人、公文字號、事由、起訖時間、計日數。
- **改動**：`class/Jill_leave.php`(`$filter_arr`/`create`/`store`/`update`)、`templates/op_jill_leave_*form/show*.tpl`、`language/*/main.php`。
- **驗收**：新假單可填寫並正確儲存/顯示全部新欄位；舊假單顯示空白不報錯。

### Phase 3｜三種遺課處理方式（核心業務）⭐
- **任務**：表單加入「處理方式」三擇一切換（代課/補課/調課），動態顯示對應欄位；`save_substitutes()` 擴充寫入新欄位；顯示頁與總覽呈現。
- **改動**：`class/Jill_leave.php`(`save_substitutes`)、`class/Jill_leave_class.php`(`display_class` 解析新欄位)、`class/Jill_leave_substitute.php`(總覽)、表單模板、`js/leave_form.js`(動態切換)、語言包。
- **驗收**：可分別建立「代課/補課/調課」三種假單並正確儲存顯示；舊資料歸類為代課。
- **規則落地**：備註 3（代課須同科目）、備註 4（調課須對方同意）以前端提示 + 欄位記錄。

### Phase 4｜三層審核流程
- **任務**：實作 `jill_leave_approve` 簽章歷程；教學組長/教務主任核章介面；`status` 與簽章歷程連動。
- **改動**：新增 `class/Jill_leave_approve.php`、`class/Jill_leave.php`(狀態連動)、核章模板、語言包。
- **權限**：教學組長/教務主任以 Email 白名單（沿用 `Tools::get_admin_email`）或群組判斷。
- **驗收**：假單可依序經三層核章；歷程完整記錄；已核章假單鎖定。

### Phase 5｜調課公告（GAS 整合）
- **任務**：擴充公告區塊與公開總覽頁，呈現「代課教師/類型/班級/原課程/異動後/請假教師」卡片式列表（參考 GAS UI）。
- **改動**：`blocks/jill_leave_show.php`、新增公告模板、`class/Jill_leave_substitute.php`(公告查詢方法)。
- **驗收**：前端公告頁可顯示近期調代課紀錄，支援類型篩選。

### Phase 6｜PDF 輸出（符合紙本表單格式）
- **任務**：調整 `pdf.php`，輸出與紙本《報告單》一致之版面（基本資料 + 三種處理方式表格 + 簽章欄）。
- **改動**：`pdf.php`、必要時引入 TCPDF（確認 PHP8 相容版本）。
- **驗收**：產出之 PDF 與紙本表單欄位、配置一致，可直接列印使用。

### Phase 7｜前端介面美化（選配）
- **任務**：以 Bootstrap 5 優化表單/列表/公告介面，提升手機版體驗（參考 GAS 卡片式設計）。
- **改動**：各模板、`css/module.css`。
- **原則**：僅改外觀，不動業務邏輯。

---

## 7. 關鍵決策點（待確認）

| # | 決策項目 | 選項 A（推薦） | 選項 B | 說明 |
|---|----------|----------------|--------|------|
| D1 | 三種處理方式建模 | 擴充 `jill_leave_class`（5.2） | 新增獨立 `jill_leave_course` 表 | A 改動最小、最漸進；B 結構最乾淨但需遷移 |
| D2 | 「時」的儲存 | `start_time`/`end_time` varchar | 升級 start_date 為 datetime | A 不破壞既有 date 邏輯 |
| D3 | 計日數型別 | `decimal(5,1)` | varchar | A 利於鐘點費統計 |
| D4 | 審核流程 | 獨立 `jill_leave_approve` 表 | 主表加 3 個核章欄位 | A 保留完整歷程與稽核 |
| D5 | 核章者身份判斷 | Email 白名單（沿用現有） | XOOPS 群組 | 視學校帳號體系決定 |
| D6 | 調課公告範圍 | 僅顯示已通過(status=1) | 含待審核 | 建議僅通過者公告 |

---

## 8. 風險與對策

| 風險 | 對策 |
|------|------|
| 升級 SQL 重複執行報錯 | onUpdate 先查 `information_schema.COLUMNS` 確認欄位存在與否 |
| 舊資料無 `handle_type` | 預設值 `substitute` + 顯示層 fallback |
| MyISAM 跨表寫入無 Transaction | 延續既有「失敗手動 DELETE」模式 |
| PDF 套件 PHP8 相容 | 選用 TCPDF 最新版，先於測試環境驗證 |
| 前端動態表單複雜度 | 以 `js/leave_form.js` 集中處理，逐步擴充 |
| 語言包遺漏 | 每階段同步更新 `language/tchinese_utf8/main.php` |

---

## 9. 交付物清單

- [x] 本規劃書（`docs/refactor_plan.md`）
- [ ] Phase 1：資料庫升級 SQL + onUpdate
- [ ] Phase 2：基本資料欄位（表單/儲存/顯示）
- [ ] Phase 3：三種遺課處理方式
- [ ] Phase 4：三層審核流程
- [ ] Phase 5：調課公告
- [ ] Phase 6：PDF 輸出
- [ ] Phase 7：前端美化（選配）

---

> 📌 **下一步**：請確認第 7 節「關鍵決策點」後，即可由 **Phase 1** 開始實作。
