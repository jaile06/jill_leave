# `jill_leave` 模組重構規劃架構書
> 基於《XOOPS 2.5 模組開發規範（dinfo / TadTools 模式）》與 PHP 8 現代化語法

## 1. 專案基本資訊
| 欄位 | 設定值 |
|------|--------|
| **模組名稱** | `jill_leave` |
| **XOOPS 版本** | 2.5.11 |
| **PHP 版本** | 8.x (使用 `??=`, `match`, `?->`, 嚴格型別宣告) |
| **MySQL 版本** | 8.0 (Collation: `utf8mb4_0900_ai_ci`, Engine: `MyISAM`) |
| **核心依賴** | `tadtools` (Utility, FormValidator, SweetAlert, TadUpFiles, My97DatePicker) |
| **命名空間** | `XoopsModules\Jill_leave\` (PSR-4 自動載入) |

---

## 2. 資料庫設計 (已確認)
全面採用 `MyISAM` 引擎以維持 XOOPS 生態系一致性，並使用 `utf8mb4_0900_ai_ci` 確保與核心表 JOIN 無礙。

| 資料表 | 用途 | 關鍵優化欄位 / 索引 |
|--------|------|---------------------|
| `jill_leave` | 請假主表 | `start_date`, `end_date`, `status` (0,1,2)；增加 `idx_uid`, `idx_dates` 索引。 |
| `jill_leave_cate` | 假別分類 | `enable` (取代 cate_enable)；增加 `idx_sort` 索引。 |
| `jill_leave_substitute`| 代課日期 | 移除冗餘的 `substitute_week`；增加 `idx_sn`, `idx_date` 索引。 |
| `jill_leave_class` | 代課節次明細 | `class_period` (取代 class 保留字)；改為獨立 PK `class_sn`。 |

---

## 3. 目錄結構 (強制規範)
```text
modules/jill_leave/
├── xoops_version.php          # 模組宣告 (整合 tadtools 區塊/選單)
├── header.php                 # 錯誤層級 + require mainfile.php + interface.php
├── footer.php                 # assign now_op + 引入 XOOPS footer
├── interface.php              # 建 $interface_menu + Tools::get_session()
├── index.php                  # 前台入口：教師請假 CRUD (程序式 switch/match)
├── cate.php                   # 前台入口：假別分類管理 (僅 leave_adm 可見)
├── substitute.php             # 前台入口：管理者代課總覽 / 匯出
├── admin/
│   ├── about.php              # 後台唯一頁面：模組資訊與說明
│   └── menu.php               # 後台選單
├── class/                     # 命名空間 PSR-4，業務邏輯 (全 public static)
│   ├── Leave.php              # 請假主表 CRUD 與跨表寫入邏輯
│   ├── Cate.php               # 假別分類 CRUD
│   ├── Substitute.php         # 代課總覽與查詢
│   ├── Tools.php              # 過濾/SQL 輔助/權限/session
│   └── Update.php             # 模組升級邏輯
├── preloads/
│   ├── autoloader.php         # PSR-4 autoloader
│   └── core.php               # 核心預載
├── blocks/                    # 區塊 (請假公告區塊)
├── templates/
│   ├── jill_leave_index.tpl   # 前台外框
│   ├── op_jill_leave_index.tpl# 請假列表
│   ├── op_jill_leave_form.tpl # 請假表單
│   ├── op_jill_leave_cate.tpl # 假別管理
│   └── sub_...                # 可重用片段
├── language/tchinese_utf8/    # 語言包 (modinfo.php / main.php / admin.php)
└── sql/mysql.sql              # 資料庫結構
```

---

## 4. 核心架構與請求生命週期
嚴格分離「路由編排」與「業務邏輯」，禁止在入口檔寫 SQL，禁止在 Class 中 echo HTML。

```text
[瀏覽器請求] 
   ↓
[入口檔 (index.php / cate.php)] 
   1. require header.php (初始化環境)
   2. 設定 $GLOBALS['xoopsOption']['template_main']
   3. require XOOPS_ROOT_PATH/header.php (渲染導覽列)
   4. $op = Request::getString('op');
   5. match ($op) { ... } (路由分發，呼叫 Class 靜態方法)
   6. require footer.php (渲染樣板)
   ↓
[Class 靜態方法 (class/Leave.php)]
   1. 接收參數，進行 Tools::filter() 過濾
   2. 組裝 SQL，使用 $xoopsDB->query() 執行
   3. 將結果 $xoopsTpl->assign() 給樣板變數
   ↓
[Smarty 樣板 (templates/op_*.tpl)]
   渲染 HTML，使用 TadTools 套件 (SweetAlert, FormValidator)
```

---

## 5. 功能模組劃分 (前台化)

### 5.1 教師請假 CRUD (入口: `index.php` / Class: `Leave.php`)
*   **列表**：依 `uid` 列出個人請假紀錄，支援分頁。
*   **表單**：新增/編輯請假單，包含代課資訊批次儲存。
*   **跨表寫入 (PHP端把關)**：因使用 MyISAM 不支援 Transaction，寫入 `jill_leave` 後，若寫入 `substitute` 或 `class` 失敗，需手動 `DELETE` 主表資料。
*   **刪除**：使用 TadTools `SweetAlert` 進行二次確認，連帶刪除代課與節次資料。

### 5.2 假別分類管理 (入口: `cate.php` / Class: `Cate.php`)
*   **權限**：僅 `$_SESSION['jill_leave_adm']` 為 true 時可存取。
*   **CRUD**：假別名稱、排序、啟用狀態。
*   **AJAX 排序**：前端拖曳排序，後端 `Cate::update_sort()` 批次更新 `cate_sort`。

### 5.3 管理者代課總覽 (入口: `substitute.php` / Class: `Substitute.php`)
*   **權限**：管理者專用。
*   **功能**：依月份篩選全校請假/代課列表、刪除任意假單。
*   **匯出**：Excel 鐘點費清冊、PDF 假單 (例外流程：直接輸出 Header 與檔案流，不走 footer 樣板)。

### 5.4 管理人員設定 (整合於 `index.php` 或獨立 `adm.php`)
*   **功能**：更新 `xoops_config` 中的管理員 Email 設定。
*   **權限**：僅 `leave_adm` 可見表單與執行更新。

### 5.5 區塊 (blocks/)
*   **請假公告區塊**：顯示 `end_date >= today` 且 `status = 1` (已通過) 的請假紀錄。

---

## 6. 開發鐵則 (AI 與開發者約束)

1.  **類別寫法**：`class/` 下的類必須加 `namespace XoopsModules\Jill_leave;`，方法一律 `public static`。
2.  **雙層過濾**：入口用 `Xmf\Request`，Class 內用 `Tools::filter($k, $v, 'write', $filter_arr)`。
3.  **安全防護**：寫入型 op 開頭必加 `Utility::xoops_security_check()`；表單必加 `Utility::token_form()`。
4.  **TadTools 強制整合**：
    *   刪除/危險操作：必用 `SweetAlert`，禁原生 `confirm()`。
    *   表單驗證：必用 `FormValidator`。
    *   檔案上傳：必用 `TadUpFiles`。
5.  **禁止事項**：
    *   ❌ 禁止在入口檔寫 SQL。
    *   ❌ 禁止在 Class 中 `echo` HTML。
    *   ❌ 禁止使用 `$$key` 可變變數。
    *   ❌ 禁止硬編碼中文字串（必須放 `language/` 語言包）。
    *   ❌ 寫入型 op 後忘記 `header("location: ..."); exit;`。

---

## 7. 實作階段規劃 (Roadmap)

*   **Phase 1: 基礎建設**
    *   建立 `header.php`, `footer.php`, `interface.php`。
    *   建立 `class/Tools.php` 與 `preloads/autoloader.php`。
*   **Phase 2: 假別分類 (驗證 CRUD 架構)**
    *   實作 `cate.php` 與 `class/Cate.php`。
    *   建立假別列表與表單樣板，驗證 TadTools 整合。
*   **Phase 3: 教師請假 (核心業務)**
    *   實作 `index.php` 與 `class/Leave.php`。
    *   處理跨表寫入 (PHP端把關) 與 SweetAlert 刪除確認。
*   **Phase 4: 管理者總覽與後台**
    *   實作 `substitute.php` 與後台 `admin/about.php`。
    *   實作管理人員設定功能。
*   **Phase 5: 進階功能**
    *   實作請假公告區塊。
    *   實作 Excel/PDF 匯出功能。
