var _app = (() => {
  // src/js/utils/lucide.js
  var renderLucideIcons = () => {
    if (!window.lucide || typeof window.lucide.createIcons !== "function") {
      return false;
    }
    window.requestAnimationFrame(() => {
      window.lucide.createIcons({ attrs: { "stroke-width": 1.8 } });
    });
    return true;
  };
  var bootLucideIcons = () => {
    if (renderLucideIcons()) return;
    let attempts = 0;
    const interval = setInterval(() => {
      attempts += 1;
      if (renderLucideIcons() || attempts >= 20) clearInterval(interval);
    }, 500);
  };

  // src/js/utils/dev.js
  var isDev = String(document.documentElement.dataset.env || "").toLowerCase() === "development";
  var devError = (...args) => {
    if (isDev) console.error(...args);
  };

  // src/js/utils/slug.js
  var slugify = (value) => String(value || "").normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").replace(/-{2,}/g, "-").slice(0, 255);
  var bootSlugFields = () => {
    document.querySelectorAll("input[data-slug-source]").forEach((slugInput) => {
      if (!(slugInput instanceof HTMLInputElement)) return;
      const sourceSelector = slugInput.dataset.slugSource || "";
      const sourceInput = sourceSelector === "" ? null : document.querySelector(sourceSelector);
      const regenerateButton = slugInput.closest("[data-slug-field]")?.querySelector("[data-slug-regenerate]");
      const checkUrl = slugInput.dataset.slugCheckUrl || "";
      const currentId = slugInput.dataset.slugCurrentId || "";
      const statusIcons = slugInput.closest("[data-slug-field]")?.querySelectorAll("[data-slug-status]") || [];
      if (!(sourceInput instanceof HTMLInputElement)) return;
      let manual = slugInput.value.trim() !== "" && slugInput.value.trim() !== slugify(sourceInput.value);
      let availabilityTimer = 0;
      let availabilityRequest = null;
      const showStatus = (status) => {
        statusIcons.forEach((icon) => {
          if (!(icon instanceof HTMLElement)) return;
          const active = icon.dataset.slugStatus === status;
          icon.classList.toggle("hidden", !active);
          icon.classList.toggle("flex", active);
        });
      };
      const checkAvailability = () => {
        window.clearTimeout(availabilityTimer);
        if (availabilityRequest !== null) {
          availabilityRequest.abort();
          availabilityRequest = null;
        }
        const slug = slugInput.value.trim();
        if (checkUrl === "" || slug.length < 2 || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
          showStatus("");
          slugInput.setCustomValidity("");
          return;
        }
        availabilityTimer = window.setTimeout(() => {
          const url = new URL(checkUrl, window.location.origin);
          url.searchParams.set("slug", slug);
          if (currentId !== "") url.searchParams.set("current_id", currentId);
          const controller = new AbortController();
          availabilityRequest = controller;
          showStatus("checking");
          fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin", signal: controller.signal }).then((response) => response.ok ? response.json() : Promise.reject(new Error(String(response.status)))).then((payload) => {
            const available = payload && payload.available === true;
            showStatus(available ? "available" : "unavailable");
            if (available) {
              slugInput.setCustomValidity("");
              return;
            }
            const unavailableIcon = Array.from(statusIcons).find(
              (icon) => icon instanceof HTMLElement && icon.dataset.slugStatus === "unavailable"
            );
            slugInput.setCustomValidity(unavailableIcon instanceof HTMLElement ? unavailableIcon.title : "Slug unavailable");
          }).catch((error) => {
            if (error && error.name === "AbortError") return;
            showStatus("");
            slugInput.setCustomValidity("");
            devError("Slug availability check failed", error);
          }).finally(() => {
            if (availabilityRequest === controller) availabilityRequest = null;
          });
        }, 350);
      };
      const syncFromSource = () => {
        if (manual) return;
        slugInput.value = slugify(sourceInput.value);
        checkAvailability();
      };
      sourceInput.addEventListener("input", syncFromSource);
      slugInput.addEventListener("input", () => {
        const normalized = slugify(slugInput.value);
        manual = normalized !== "" && normalized !== slugify(sourceInput.value);
        slugInput.value = normalized;
        checkAvailability();
      });
      if (regenerateButton instanceof HTMLButtonElement) {
        regenerateButton.addEventListener("click", () => {
          manual = false;
          syncFromSource();
          slugInput.focus();
        });
      }
      syncFromSource();
      checkAvailability();
    });
  };

  // src/js/utils/url.js
  var queryToObject = (search) => {
    const params = new URLSearchParams(search);
    const query = {};
    params.forEach((value, key) => {
      const trimmed = value.trim();
      if (trimmed !== "") query[key] = trimmed;
    });
    return query;
  };
  var objectToQueryString = (query) => {
    const params = new URLSearchParams();
    Object.entries(query || {}).forEach(([key, value]) => {
      if (typeof value === "string" && value.trim() !== "") {
        params.append(key, value.trim());
      }
    });
    return params.toString();
  };
  var formToQuery = (form) => {
    const formData = new FormData(form);
    const query = {};
    formData.forEach((value, key) => {
      if (typeof value !== "string") return;
      const trimmed = value.trim();
      if (trimmed !== "") query[key] = trimmed;
    });
    return query;
  };
  var isObject = (value) => value !== null && typeof value === "object" && !Array.isArray(value);
  var tablePayloadRoot = (payload) => {
    if (Array.isArray(payload)) return { data: payload };
    if (!isObject(payload)) return {};
    const nested = payload.data;
    if (!isObject(nested)) return payload;
    if (Array.isArray(nested.data) || isObject(nested.meta) || nested.current_page !== void 0 || nested.page !== void 0 || nested.last_page !== void 0 || nested.total_items !== void 0 || isObject(nested.summary)) {
      return nested;
    }
    return payload;
  };

  // src/js/utils/fileUrl.js
  var bestFilePreviewUrl = (file) => {
    const variants = isObject(file?.variants) ? file.variants : {};
    return String(
      variants.md?.url || variants.sm?.url || variants.thumb?.url || file?.url || ""
    );
  };
  var resolveTranslatableFilePreviewUrl = (fileId, fileUrl = "") => {
    const normalizedUrl = String(fileUrl || "");
    if (normalizedUrl !== "") return normalizedUrl;
    const normalizedId = String(fileId || "");
    return normalizedId !== "" ? `${window.location.origin}/files/${encodeURIComponent(normalizedId)}/view` : "";
  };

  // src/js/utils/labels.js
  var localePrefix = () => String(document.documentElement?.lang || "es").toLowerCase().startsWith("en") ? "en" : "es";
  var localeTag = () => localePrefix() === "en" ? "en-US" : "es-ES";
  var focusableSelector = 'a[href], button:not([disabled]), textarea, input:not([type="hidden"]):not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
  var uiLabels = {
    es: {
      confirmAction: "Confirmar acci\xF3n",
      confirm: "Confirmar",
      confirmDeleteNamed: '\xBFSeguro que quieres eliminar "{item}"?',
      confirmDeleteFallback: "\xBFSeguro que quieres eliminar este elemento?",
      requestFailed: "La solicitud fall\xF3 (HTTP {status}).",
      loadRetry: "No se pudo cargar la informaci\xF3n. Intenta nuevamente.",
      refreshing: "Actualizando resultados...",
      readonlyNotice: "Esta pantalla es de solo lectura."
    },
    en: {
      confirmAction: "Confirm action",
      confirm: "Confirm",
      confirmDeleteNamed: 'Are you sure you want to delete "{item}"?',
      confirmDeleteFallback: "Are you sure you want to delete this item?",
      requestFailed: "Request failed (HTTP {status}).",
      loadRetry: "Could not load the information. Please try again.",
      refreshing: "Refreshing results...",
      readonlyNotice: "This screen is read-only."
    }
  };
  var buildConfirmDeleteMessage = (itemLabel = "", fallback = "") => {
    const labels = uiLabels[localePrefix()] || uiLabels.es;
    const label = String(itemLabel ?? "").trim();
    if (label === "") return String(fallback || labels.confirmDeleteFallback || labels.confirm);
    return String(labels.confirmDeleteNamed || labels.confirmDeleteFallback || labels.confirm).replace("{item}", label);
  };
  var paginationLabels = {
    es: { visibleResults: "Resultados visibles", showing: "Mostrando", of: "de" },
    en: { visibleResults: "Visible results", showing: "Showing", of: "of" }
  };
  var statusLabels = {
    es: { active: "Activo", pending: "Pendiente", pending_approval: "Pendiente de aprobacion", suspended: "Suspendido", approved: "Aprobado", rejected: "Rechazado", processing: "Procesando", success: "Exitoso", failed: "Fallido" },
    en: { active: "Active", pending: "Pending", pending_approval: "Pending approval", suspended: "Suspended", approved: "Approved", rejected: "Rejected", processing: "Processing", success: "Success", failed: "Failed" }
  };
  var auditActionLabels = {
    es: { create: "Crear", update: "Actualizar", delete: "Eliminar", login: "Iniciar sesion", login_success: "Inicio de sesion exitoso", login_failure: "Inicio de sesion fallido", logout: "Cerrar sesion", approve: "Aprobar" },
    en: { create: "Create", update: "Update", delete: "Delete", login: "Login", login_success: "Login Success", login_failure: "Login Failure", logout: "Logout", approve: "Approve" }
  };
  var auditResultLabels = {
    es: { success: "Exito", failure: "Fallo", denied: "Denegado" },
    en: { success: "Success", failure: "Failure", denied: "Denied" }
  };
  var auditSeverityLabels = {
    es: { info: "Info", warning: "Advertencia", critical: "Critico" },
    en: { info: "Info", warning: "Warning", critical: "Critical" }
  };
  var statusLabel = (status) => {
    const value = String(status || "").trim();
    if (value === "") return "-";
    return statusLabels[localePrefix()]?.[value.toLowerCase()] || value;
  };
  var auditActionLabel = (action) => {
    const value = String(action || "").trim();
    if (value === "") return "-";
    return auditActionLabels[localePrefix()]?.[value.toLowerCase()] || value;
  };
  var auditResultLabel = (result) => {
    const value = String(result || "").trim();
    if (value === "") return "-";
    return auditResultLabels[localePrefix()]?.[value.toLowerCase()] || value;
  };
  var auditSeverityLabel = (severity) => {
    const value = String(severity || "").trim();
    if (value === "") return "-";
    return auditSeverityLabels[localePrefix()]?.[value.toLowerCase()] || value;
  };

  // src/js/stores/confirm.store.js
  var confirmStore = () => {
    const text = uiLabels[localePrefix()] || uiLabels.es;
    return {
      open: false,
      accepting: false,
      title: text.confirmAction,
      message: "",
      onAccept: null,
      show(message, onAccept, title = text.confirmAction) {
        this.open = true;
        this.accepting = false;
        this.message = message;
        this.title = title;
        this.onAccept = onAccept;
        requestAnimationFrame(() => {
          const dialog = document.getElementById("confirm-dialog-panel");
          if (!(dialog instanceof HTMLElement)) return;
          const focusable = dialog.querySelector(focusableSelector);
          if (focusable instanceof HTMLElement) {
            focusable.focus();
            return;
          }
          dialog.focus();
        });
      },
      close() {
        this.open = false;
        this.accepting = false;
        this.message = "";
        this.onAccept = null;
      },
      accept() {
        this.accepting = true;
        if (typeof this.onAccept === "function") this.onAccept();
        const self = this;
        setTimeout(() => {
          if (self.accepting) self.close();
        }, 5e3);
      },
      handleTab(event, container) {
        if (!(container instanceof HTMLElement)) return;
        const focusable = Array.from(container.querySelectorAll(focusableSelector)).filter((el) => el instanceof HTMLElement && !el.hasAttribute("disabled"));
        if (focusable.length === 0) {
          container.focus();
          return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        const active = document.activeElement;
        if (event.shiftKey && active === first) {
          event.preventDefault();
          last.focus();
          return;
        }
        if (!event.shiftKey && active === last) {
          event.preventDefault();
          first.focus();
        }
      }
    };
  };

  // src/js/stores/toast.store.js
  var toastStore = {
    items: [],
    push(type, message) {
      const id = Date.now() + Math.random();
      this.items.push({ id, type, message });
      setTimeout(() => {
        this.remove(id);
      }, 5e3);
    },
    remove(id) {
      this.items = this.items.filter((item) => item.id !== id);
    }
  };

  // src/js/stores/filePicker.store.js
  var filePickerStore = {
    open: false,
    activeTab: "library",
    files: [],
    loading: false,
    error: false,
    errorMessage: "",
    search: "",
    _searchDebounce: null,
    filterType: "",
    showFilterTabs: true,
    thumbSize: 120,
    multiSelect: false,
    selected: [],
    pagination: { current_page: 1, last_page: 1, total_items: 0, per_page: 24 },
    dragging: false,
    uploading: false,
    uploadProgress: 0,
    uploadFileName: "",
    uploadError: "",
    _uploadFile: null,
    inputAccept: "",
    _onSelect: null,
    _onSelectMulti: null,
    show(options = {}) {
      this.open = true;
      this.multiSelect = Boolean(options.multi);
      this.showFilterTabs = options.showFilterTabs !== false;
      this.filterType = String(options.filterType || "");
      this.inputAccept = String(options.accept || "");
      this._onSelect = typeof options.onSelect === "function" ? options.onSelect : null;
      this._onSelectMulti = typeof options.onSelectMulti === "function" ? options.onSelectMulti : null;
      this.activeTab = "library";
      this.search = "";
      this.selected = [];
      this.uploadFileName = "";
      this.uploadError = "";
      this.uploading = false;
      this.uploadProgress = 0;
      this._uploadFile = null;
      this.files = [];
      this.loadFiles(1);
      requestAnimationFrame(() => {
        const panel = document.getElementById("file-picker-panel");
        if (panel instanceof HTMLElement) panel.focus();
      });
    },
    close() {
      this.open = false;
      this._onSelect = null;
      this._onSelectMulti = null;
    },
    switchTab(tab) {
      this.activeTab = tab;
      if (tab === "library" && this.files.length === 0) this.loadFiles(1);
    },
    setSearch(value) {
      this.search = String(value || "");
      clearTimeout(this._searchDebounce);
      this._searchDebounce = setTimeout(() => {
        this.loadFiles(1);
      }, 350);
    },
    setFilterType(type) {
      this.filterType = String(type || "");
      this.loadFiles(1);
    },
    changePage(page) {
      const bounded = Math.max(1, Math.min(this.pagination.last_page || 1, page));
      if (bounded !== this.pagination.current_page) this.loadFiles(bounded);
    },
    _panel() {
      return document.getElementById("file-picker-panel");
    },
    async loadFiles(page = 1) {
      this.loading = true;
      this.error = false;
      this.errorMessage = "";
      const panel = this._panel();
      const dataUrl = String(panel?.dataset?.dataUrl || "/files/picker-data");
      const params = new URLSearchParams({ page: String(page), per_page: String(this.pagination.per_page || 24) });
      if (this.search.trim() !== "") params.set("search", this.search.trim());
      if (this.filterType !== "") params.set("category", this.filterType);
      try {
        const resp = await fetch(`${dataUrl}?${params.toString()}`, {
          credentials: "include",
          headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
        });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const payload = await resp.json();
        const apiWrapper = isObject(payload?.data?.data) ? payload.data.data : isObject(payload?.data) ? payload.data : {};
        const files = Array.isArray(apiWrapper?.data) ? apiWrapper.data : [];
        const meta = isObject(apiWrapper?.meta) ? apiWrapper.meta : {};
        this.files = files;
        this.pagination = {
          current_page: Number(meta.current_page ?? page),
          last_page: Math.max(1, Number(meta.last_page ?? 1)),
          total_items: Number(meta.total_items ?? meta.total ?? files.length),
          per_page: Number(meta.per_page ?? meta.limit ?? 24)
        };
      } catch (err) {
        devError("[filePicker] loadFiles error:", err);
        this.error = true;
        this.errorMessage = (uiLabels[localePrefix()] || uiLabels.es).loadRetry;
        this.files = [];
      } finally {
        this.loading = false;
      }
    },
    isSelected(file) {
      return this.selected.some((f) => String(f.id) === String(file.id));
    },
    toggleSelected(file) {
      if (this.isSelected(file)) {
        this.selected = this.selected.filter((f) => String(f.id) !== String(file.id));
      } else {
        this.selected.push(file);
      }
    },
    select(file) {
      if (this.multiSelect) {
        this.toggleSelected(file);
      } else {
        if (typeof this._onSelect === "function") this._onSelect(file);
        this.close();
      }
    },
    async _enrichFileWithMetadata(file) {
      return file;
    },
    async confirm() {
      if (typeof this._onSelectMulti === "function") {
        const enrichedFiles = await Promise.all(this.selected.map((f) => this._enrichFileWithMetadata(f)));
        this._onSelectMulti(enrichedFiles);
      }
      this.close();
    },
    onUploadFileChange(event) {
      const file = event?.target?.files?.[0] ?? null;
      if (file) {
        this._uploadFile = file;
        this.uploadFileName = file.name;
        this.uploadError = "";
      } else {
        this._uploadFile = null;
        this.uploadFileName = "";
      }
    },
    async submitUpload() {
      if (!this._uploadFile || this.uploading) return;
      this.uploading = true;
      this.uploadProgress = 0;
      this.uploadError = "";
      const panel = this._panel();
      const uploadUrl = String(panel?.dataset?.uploadUrl || "/files/upload");
      const csrfName = String(panel?.dataset?.csrfName || "");
      const csrfHash = String(panel?.dataset?.csrfHash || "");
      const formData = new FormData();
      formData.append("file", this._uploadFile);
      if (csrfName !== "" && csrfHash !== "") formData.append(csrfName, csrfHash);
      try {
        await new Promise((resolve, reject) => {
          const xhr = new XMLHttpRequest();
          xhr.upload.addEventListener("progress", (e) => {
            if (e.lengthComputable) this.uploadProgress = Math.round(e.loaded / e.total * 90);
          });
          xhr.open("POST", uploadUrl);
          xhr.setRequestHeader("Accept", "application/json");
          xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
          xhr.onload = () => {
            let json = null;
            try {
              json = JSON.parse(xhr.responseText);
            } catch {
            }
            if (json?.csrf_name && json?.csrf_hash && panel) {
              panel.dataset.csrfName = String(json.csrf_name);
              panel.dataset.csrfHash = String(json.csrf_hash);
            }
            if (xhr.status >= 200 && xhr.status < 300 && json?.ok !== false) {
              resolve(json);
            } else {
              const msg = json?.messages?.[0] || json?.message || `HTTP ${xhr.status}`;
              reject(new Error(String(msg)));
            }
          };
          xhr.onerror = () => reject(new Error("Network error"));
          xhr.send(formData);
        });
        this.uploadProgress = 100;
        this._uploadFile = null;
        this.uploadFileName = "";
        this.switchTab("library");
        this.loadFiles(1);
      } catch (err) {
        devError("[filePicker] submitUpload error:", err);
        this.uploadError = err instanceof Error ? err.message : "Upload failed.";
      } finally {
        this.uploading = false;
        this.uploadProgress = 0;
      }
    }
  };

  // src/js/components/appShell.js
  var appShell = () => ({
    sidebarOpen: window.innerWidth >= 768,
    init() {
      this.$watch("sidebarOpen", (isOpen) => {
        if (isOpen) this.queueScrollActiveSidebarItem();
      });
      this.queueScrollActiveSidebarItem();
    },
    queueScrollActiveSidebarItem() {
      if (window.innerWidth < 768 && !this.sidebarOpen) return;
      window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
          this.scrollActiveSidebarItem();
        });
      });
    },
    scrollActiveSidebarItem() {
      const sidebar = document.getElementById("app-sidebar");
      if (!sidebar) return;
      const scrollContainer = sidebar.querySelector("nav");
      const activeItem = sidebar.querySelector("a.bg-brand-50.text-brand-700");
      if (!scrollContainer || !activeItem) return;
      const containerRect = scrollContainer.getBoundingClientRect();
      const itemRect = activeItem.getBoundingClientRect();
      const padding = 24;
      const isOutOfView = itemRect.top < containerRect.top + padding || itemRect.bottom > containerRect.bottom - padding;
      if (!isOutOfView) return;
      const behavior = window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth";
      activeItem.scrollIntoView({ block: "center", inline: "nearest", behavior });
    }
  });

  // src/js/utils/badges.js
  var statusBadgeClass = (status) => {
    const val = String(status || "").toLowerCase();
    if (["active", "approved", "success"].includes(val)) return "bg-green-100 text-green-800";
    if (["pending", "pending_approval", "processing"].includes(val)) return "bg-yellow-100 text-yellow-800";
    if (["suspended", "rejected", "failed"].includes(val)) return "bg-red-100 text-red-800";
    return "bg-gray-100 text-gray-800";
  };
  var auditActionBadgeClass = (action) => {
    const val = String(action || "").toLowerCase();
    if (val === "create") return "bg-green-100 text-green-800";
    if (val === "update") return "bg-blue-100 text-blue-800";
    if (val === "delete") return "bg-red-100 text-red-800";
    if (["login", "login_success"].includes(val)) return "bg-brand-100 text-brand-800";
    if (val === "login_failure") return "bg-red-100 text-red-800";
    if (val === "logout") return "bg-gray-100 text-gray-800";
    if (val === "approve") return "bg-emerald-100 text-emerald-800";
    return "bg-gray-100 text-gray-700";
  };
  var auditResultBadgeClass = (result) => {
    const val = String(result || "").toLowerCase();
    if (val === "success") return "bg-green-100 text-green-800";
    if (val === "failure") return "bg-red-100 text-red-800";
    if (val === "denied") return "bg-orange-100 text-orange-800";
    return "bg-gray-100 text-gray-700";
  };
  var auditSeverityBadgeClass = (severity) => {
    const val = String(severity || "").toLowerCase();
    if (val === "info") return "bg-blue-50 text-blue-700 border border-blue-200";
    if (val === "warning") return "bg-amber-50 text-amber-700 border border-amber-200";
    if (val === "critical") return "bg-red-100 text-red-700 border border-red-300 font-bold";
    return "bg-gray-100 text-gray-600 border border-gray-200";
  };

  // src/js/utils/date.js
  var toDateInput = (value) => {
    if (value === null || value === void 0) return null;
    if (typeof value === "string" || typeof value === "number") return value;
    if (Array.isArray(value)) return value.length > 0 ? toDateInput(value[0]) : null;
    if (typeof value === "object") {
      if (typeof value.date === "string" || typeof value.date === "number") return value.date;
      if (typeof value.datetime === "string" || typeof value.datetime === "number") return value.datetime;
      if (typeof value.created_at === "string" || typeof value.created_at === "number") return value.created_at;
      if (typeof value.value === "string" || typeof value.value === "number") return value.value;
    }
    return null;
  };
  var formatDate = (value) => {
    const candidate = toDateInput(value);
    if (candidate === null || candidate === "") return "-";
    const date = new Date(candidate);
    if (Number.isNaN(date.getTime())) return String(candidate);
    return new Intl.DateTimeFormat(localeTag(), {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit"
    }).format(date);
  };

  // src/js/components/remoteTable.js
  var remoteTableFactory = (config = {}) => {
    const text = uiLabels[localePrefix()] || uiLabels.es;
    return {
      apiUrl: config.apiUrl || window.location.pathname,
      pageUrl: config.pageUrl || window.location.pathname,
      mode: config.mode || "generic",
      routes: config.routes || {},
      csrf: config.csrf || { name: "", hash: "" },
      limitOptions: Array.isArray(config.limitOptions) && config.limitOptions.length > 0 ? config.limitOptions : ["10", "25", "50", "100"],
      confirmDelete: config.confirmDelete || text.confirm,
      loading: false,
      error: false,
      errorMessage: "",
      rows: [],
      summary: {},
      pagination: { mode: "page", current_page: 1, last_page: 1, total_items: 0, limit: 25, from: 0, to: 0, next_cursor: "", prev_cursor: "" },
      page_input: "1",
      query: {},
      filterDefaults: {},
      filterFields: /* @__PURE__ */ new Set(),
      ignoredFilterKeys: /* @__PURE__ */ new Set(["sort", "page", "cursor", "order_by", "order_dir", "per_page", "limit"]),
      requestId: 0,
      debounceTimers: /* @__PURE__ */ new WeakMap(),
      form: null,
      init() {
        this.form = this.$el.querySelector('form[data-table-filter-form="1"]');
        this.loadFilterConfig();
        const fromUrl = queryToObject(window.location.search);
        this.query = { ...this.defaultFilterQuery(), ...fromUrl };
        this.applyQueryToForm();
        this.bindFormEvents();
        this.fetchData(false);
        window.addEventListener("popstate", () => {
          this.query = { ...this.defaultFilterQuery(), ...queryToObject(window.location.search) };
          this.applyQueryToForm();
          this.fetchData(false);
        });
      },
      loadFilterConfig() {
        this.filterDefaults = {};
        this.filterFields = /* @__PURE__ */ new Set();
        this.ignoredFilterKeys = /* @__PURE__ */ new Set(["sort", "page", "cursor"]);
        if (!this.form) return;
        this.form.querySelectorAll("input[name], select[name], textarea[name]").forEach((el) => {
          const name = el.getAttribute("name");
          if (!name) return;
          this.filterFields.add(name);
          if (this.filterDefaults[name] === void 0) this.filterDefaults[name] = "";
        });
        const defaultsRaw = String(this.form.dataset.filterDefaults || "").trim();
        if (defaultsRaw !== "") {
          try {
            const parsed = JSON.parse(defaultsRaw);
            if (isObject(parsed)) {
              Object.entries(parsed).forEach(([key, value]) => {
                if (typeof key !== "string" || key.trim() === "") return;
                this.filterFields.add(key);
                this.filterDefaults[key] = String(value ?? "").trim();
              });
            }
          } catch {
            return;
          }
        }
        const ignoredRaw = String(this.form.dataset.filterIgnored || "").trim();
        if (ignoredRaw !== "") {
          try {
            const parsed = JSON.parse(ignoredRaw);
            if (Array.isArray(parsed)) {
              parsed.forEach((key) => {
                if (typeof key === "string" && key.trim() !== "") this.ignoredFilterKeys.add(key);
              });
            }
          } catch {
            return;
          }
        }
      },
      hasActiveFilters() {
        if (!this.form || this.form.dataset.reactiveHasFilters !== "1") return false;
        const keys = /* @__PURE__ */ new Set([...Array.from(this.filterFields), ...Object.keys(this.query || {})]);
        for (const key of keys) {
          if (this.ignoredFilterKeys.has(key)) continue;
          if (!this.filterFields.has(key) && this.filterDefaults[key] === void 0) continue;
          const defaultValue = String(this.filterDefaults[key] ?? "").trim();
          const currentValue = Object.prototype.hasOwnProperty.call(this.query, key) ? String(this.query[key] ?? "").trim() : "";
          if (currentValue !== defaultValue) return true;
        }
        return false;
      },
      defaultFilterQuery() {
        const query = {};
        Object.entries(this.filterDefaults || {}).forEach(([key, value]) => {
          const normalized = String(value ?? "").trim();
          if (normalized !== "") query[key] = normalized;
        });
        return query;
      },
      bindFormEvents() {
        if (!this.form) return;
        this.form.addEventListener("submit", (event) => {
          event.preventDefault();
          const activeSort = typeof this.query.sort === "string" ? this.query.sort : "";
          this.query = formToQuery(this.form);
          if (activeSort !== "") this.query.sort = activeSort;
          this.query.page = "";
          this.query.cursor = "";
          this.fetchData(true);
        });
        this.form.querySelectorAll("[data-table-debounce]").forEach((input) => {
          input.addEventListener("input", () => {
            const previousTimer = this.debounceTimers.get(input);
            if (previousTimer) clearTimeout(previousTimer);
            const wait = Number.parseInt(input.dataset.tableDebounce || "350", 10);
            const timer = setTimeout(() => {
              const activeSort = typeof this.query.sort === "string" ? this.query.sort : "";
              this.query = formToQuery(this.form);
              if (activeSort !== "") this.query.sort = activeSort;
              this.query.page = "";
              this.query.cursor = "";
              this.fetchData(true);
            }, Number.isFinite(wait) ? wait : 350);
            this.debounceTimers.set(input, timer);
          });
        });
      },
      applyQueryToForm() {
        if (!this.form) return;
        this.form.querySelectorAll("input[name], select[name], textarea[name]").forEach((el) => {
          const name = el.getAttribute("name");
          if (!name) return;
          const value = this.query[name] ?? "";
          if (el.type === "checkbox" || el.type === "radio") {
            el.checked = String(el.value) === value;
          } else {
            el.value = value;
          }
        });
      },
      buildUrl(base, query) {
        const url = new URL(base, window.location.origin);
        url.search = objectToQueryString(query);
        return url.toString();
      },
      async fetchData(pushHistory = true) {
        this.loading = true;
        this.error = false;
        this.errorMessage = "";
        this.requestId += 1;
        const requestId = this.requestId;
        const apiUrl = this.buildUrl(this.apiUrl, this.query);
        const pageUrl = this.buildUrl(this.pageUrl, this.query);
        const pageText = uiLabels[localePrefix()] || uiLabels.es;
        try {
          const response = await fetch(apiUrl, {
            credentials: "include",
            headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
          });
          const rawBody = await response.text();
          let payload = {};
          if (rawBody.trim() !== "") {
            try {
              payload = JSON.parse(rawBody);
            } catch (e) {
              devError("JSON Parse error in fetchData:", e);
              if (requestId === this.requestId) {
                this.rows = [];
                this.error = true;
                this.errorMessage = pageText.loadRetry;
              }
              return;
            }
          }
          if (requestId !== this.requestId) return;
          if (!response.ok) {
            this.rows = [];
            this.summary = {};
            this.pagination = { mode: "page", current_page: 1, last_page: 1, total_items: 0, limit: 25, from: 0, to: 0, next_cursor: "", prev_cursor: "" };
            this.page_input = "1";
            this.error = true;
            this.errorMessage = this.resolveErrorMessage(payload, response.status);
            return;
          }
          const root = tablePayloadRoot(payload);
          this.rows = this.extractRows(root);
          this.summary = this.extractSummary(root);
          this.pagination = this.extractPagination(root, this.rows.length);
          this.page_input = String(this.pagination.current_page);
          this.$nextTick(() => {
            bootLucideIcons();
          });
          if (pushHistory) window.history.pushState({}, "", pageUrl);
        } catch (err) {
          devError("Fetch error in fetchData:", err);
          if (requestId !== this.requestId) return;
          this.rows = [];
          this.summary = {};
          this.error = true;
          this.errorMessage = pageText.loadRetry;
          this.page_input = "1";
        } finally {
          if (requestId === this.requestId) this.loading = false;
        }
      },
      extractRows(root) {
        if (Array.isArray(root.data)) return root.data;
        if (isObject(root.data) && Array.isArray(root.data.data)) return root.data.data;
        if (Array.isArray(root.items)) return root.items;
        const commonKeys = ["users", "files", "audit", "api_keys", "keys", "logs", "entries"];
        for (const key of commonKeys) {
          if (Array.isArray(root[key])) return root[key];
          if (isObject(root.data) && Array.isArray(root.data[key])) return root.data[key];
        }
        return [];
      },
      extractSummary(root) {
        if (isObject(root.summary)) return root.summary;
        if (isObject(root.data) && isObject(root.data.summary)) return root.data.summary;
        return {};
      },
      extractPagination(root, visibleCount) {
        const meta = isObject(root.meta) ? root.meta : {};
        const next_cursor = String(meta.next_cursor ?? root.next_cursor ?? "");
        const prev_cursor = String(meta.prev_cursor ?? root.prev_cursor ?? "");
        const hasCursor = next_cursor !== "" || prev_cursor !== "" || String(this.query.cursor || "") !== "";
        const limit = Number(meta.per_page ?? meta.limit ?? root.per_page ?? root.limit ?? this.query.limit ?? this.query.per_page ?? 25) || 25;
        const safeLimit = Math.max(1, limit);
        const total = Number(meta.total_items ?? meta.total ?? root.total_items ?? root.total ?? visibleCount) || visibleCount;
        const current_page = Number(meta.current_page ?? meta.page ?? root.current_page ?? root.page ?? this.query.page ?? 1) || 1;
        const derivedLastPage = Math.max(1, Math.ceil(Math.max(0, total) / safeLimit));
        const last_page = Number(meta.last_page ?? root.last_page ?? derivedLastPage) || derivedLastPage;
        const normalizedCurrentPage = Math.max(1, Math.min(current_page, Math.max(1, last_page)));
        const from = total <= 0 ? 0 : (normalizedCurrentPage - 1) * safeLimit + 1;
        let to = 0;
        if (total > 0) {
          to = visibleCount > 0 ? Math.min(total, from + visibleCount - 1) : Math.min(total, normalizedCurrentPage * safeLimit);
        }
        return {
          mode: hasCursor ? "cursor" : "page",
          current_page: normalizedCurrentPage,
          last_page: Math.max(1, last_page),
          total_items: Math.max(0, total),
          limit: safeLimit,
          from: Math.max(0, from),
          to: Math.max(0, to),
          next_cursor,
          prev_cursor
        };
      },
      resolveErrorMessage(payload, status) {
        if (isObject(payload)) {
          if (typeof payload.message === "string" && payload.message.trim() !== "") return payload.message;
          if (Array.isArray(payload.messages) && payload.messages.length > 0) return String(payload.messages[0]);
        }
        return text.requestFailed.replace("{status}", String(status));
      },
      isCursorMode() {
        return this.pagination.mode === "cursor";
      },
      hasPagination() {
        if (this.isCursorMode()) return this.pagination.prev_cursor !== "" || this.pagination.next_cursor !== "";
        return this.pagination.last_page > 1;
      },
      pageWindow() {
        const start = Math.max(1, this.pagination.current_page - 2);
        const end = Math.min(this.pagination.last_page, this.pagination.current_page + 2);
        const pages = [];
        for (let page = start; page <= end; page += 1) pages.push(page);
        return pages;
      },
      paginationLabel() {
        const labels = paginationLabels[localePrefix()] || paginationLabels.es;
        if (this.isCursorMode()) return `${labels.visibleResults}: ${this.pagination.total_items}`;
        if (this.pagination.total_items <= 0 || this.pagination.from <= 0) return `${labels.showing} 0 ${labels.of} ${this.pagination.total_items}`;
        return `${labels.showing} ${this.pagination.from}-${this.pagination.to} ${labels.of} ${this.pagination.total_items}`;
      },
      paginationLimitOptions() {
        const options = [];
        this.limitOptions.forEach((value) => {
          const parsed = Number.parseInt(String(value ?? ""), 10);
          if (!Number.isFinite(parsed) || parsed <= 0) return;
          options.push(parsed);
        });
        if (options.length === 0) return [10, 25, 50, 100];
        return Array.from(new Set(options)).sort((a, b) => a - b);
      },
      currentSort(field) {
        const sort = String(this.query.sort || "");
        if (sort === field) return "asc";
        if (sort === `-${field}`) return "desc";
        return "";
      },
      sortAria(field) {
        const d = this.currentSort(field);
        return d === "asc" ? "ascending" : d === "desc" ? "descending" : "none";
      },
      sortIcon(field) {
        const d = this.currentSort(field);
        return d === "asc" ? "\u2191" : d === "desc" ? "\u2193" : "\u2195";
      },
      toggleSort(field) {
        const current = this.currentSort(field);
        this.query.sort = current === "asc" ? `-${field}` : current === "desc" ? "" : field;
        this.query.page = "";
        this.query.cursor = "";
        this.fetchData(true);
      },
      goToPage(page) {
        const boundedPage = Math.max(1, Math.min(this.pagination.last_page || 1, page));
        this.query.page = String(boundedPage);
        this.query.cursor = "";
        this.page_input = String(boundedPage);
        this.fetchData(true);
      },
      goToFirstPage() {
        if (!this.isCursorMode() && this.pagination.current_page > 1) this.goToPage(1);
      },
      goToLastPage() {
        if (!this.isCursorMode() && this.pagination.current_page < this.pagination.last_page) this.goToPage(this.pagination.last_page);
      },
      goToPageFromInput() {
        if (this.isCursorMode()) return;
        const page = Number.parseInt(String(this.page_input || ""), 10);
        if (!Number.isFinite(page) || page <= 0) {
          this.page_input = String(this.pagination.current_page);
          return;
        }
        this.goToPage(page);
      },
      goToCursor(cursor) {
        if (!cursor) return;
        this.query.cursor = String(cursor);
        this.query.page = "";
        this.fetchData(true);
      },
      onLimitChange(limit) {
        const parsed = Number.parseInt(String(limit || ""), 10);
        if (!Number.isFinite(parsed) || parsed <= 0) {
          this.query.limit = "";
        } else {
          const maxOption = Math.max(...this.paginationLimitOptions());
          this.query.limit = String(Math.min(maxOption, Math.max(1, parsed)));
        }
        this.query.page = "";
        this.query.cursor = "";
        this.page_input = "1";
        this.fetchData(true);
      },
      fullName(row) {
        const fullName = `${String(row.first_name ?? "").trim()} ${String(row.last_name ?? "").trim()}`.trim();
        return fullName === "" ? "-" : fullName;
      },
      statusBadgeClass,
      statusLabel,
      auditActionBadgeClass,
      auditActionLabel,
      auditResultBadgeClass,
      auditResultLabel,
      auditSeverityBadgeClass,
      auditSeverityLabel,
      formatDate,
      showUrl(id) {
        return `${this.routes.showBase}/${encodeURIComponent(String(id ?? ""))}`;
      },
      editUrl(id) {
        return `${this.routes.editBase}/${encodeURIComponent(String(id ?? ""))}/edit`;
      },
      userShowUrl(id) {
        return `${this.routes.showBase}/${encodeURIComponent(String(id ?? ""))}`;
      },
      userEditUrl(id) {
        return `${this.routes.editBase}/${encodeURIComponent(String(id ?? ""))}/edit`;
      },
      auditShowUrl(id) {
        return `${this.routes.showBase}/${encodeURIComponent(String(id ?? ""))}`;
      },
      fileDownloadUrl(id) {
        return `${this.routes.downloadBase}/${encodeURIComponent(String(id ?? ""))}/download`;
      },
      fileDeleteUrl(id) {
        return `${this.routes.deleteBase}/${encodeURIComponent(String(id ?? ""))}/delete`;
      },
      apiKeyShowUrl(id) {
        return `${this.routes.showBase}/${encodeURIComponent(String(id ?? ""))}`;
      },
      apiKeyEditUrl(id) {
        return `${this.routes.editBase}/${encodeURIComponent(String(id ?? ""))}/edit`;
      }
    };
  };

  // src/js/components/formFieldBuilder.js
  var formFieldBuilderFactory = (config = {}) => {
    const readJsonList = (elementId) => {
      const element = elementId ? document.getElementById(elementId) : null;
      if (!element) return [];
      try {
        const parsed = JSON.parse(element.textContent || "[]");
        return Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        console.warn("[forms] Could not parse field builder JSON data.", error);
        return [];
      }
    };
    const languages = Array.isArray(config.languages) ? config.languages : readJsonList(config.languagesElementId);
    const initialFields = Array.isArray(config.initialFields) ? config.initialFields : readJsonList(config.initialFieldsElementId);
    const toBoolean = (value) => value === true || value === 1 || value === "1";
    const normalizeField = (field) => ({
      ...field,
      is_required: toBoolean(field?.is_required),
      is_active: toBoolean(field?.is_active)
    });
    const defaultTranslations = () => {
      const translations = {};
      languages.forEach((lang) => {
        translations[lang.id] = { label: "", placeholder: "", help_text: "" };
      });
      return translations;
    };
    return {
      fields: initialFields.map(normalizeField),
      showModal: false,
      editingField: null,
      activeFieldLang: String(languages[0]?.code || "es"),
      fieldError: "",
      fieldForm: { field_key: "", field_type: "text", is_required: false, is_active: true, translations: defaultTranslations() },
      init() {
        this.$watch("showModal", (value) => {
          if (!value) this.fieldError = "";
        });
      },
      defaultFieldForm() {
        return { field_key: "", field_type: "text", is_required: false, is_active: true, translations: defaultTranslations() };
      },
      openCreate() {
        this.editingField = null;
        this.fieldForm = this.defaultFieldForm();
        this.activeFieldLang = String(languages[0]?.code || "es");
        this.showModal = true;
      },
      openEdit(field) {
        this.editingField = field;
        const translations = {};
        languages.forEach((lang) => {
          const existing = (field.translations || []).find((item) => item.language_id == lang.id) || {};
          translations[lang.id] = { label: existing.label || "", placeholder: existing.placeholder || "", help_text: existing.help_text || "" };
        });
        this.fieldForm = { field_key: field.field_key, field_type: field.field_type, is_required: toBoolean(field.is_required), is_active: toBoolean(field.is_active), translations };
        this.activeFieldLang = String(languages[0]?.code || "es");
        this.showModal = true;
      },
      closeModal() {
        this.showModal = false;
        this.editingField = null;
      },
      buildTranslationsArray() {
        return Object.entries(this.fieldForm.translations).map(([languageId, translation]) => ({
          language_id: parseInt(languageId, 10),
          ...translation
        }));
      },
      async saveField() {
        this.fieldError = "";
        const fieldKey = String(this.fieldForm.field_key || "").trim();
        if (fieldKey === "") {
          this.fieldError = config.fieldKeyRequiredMessage || "The field key is required.";
          return;
        }
        const payload = { field_key: fieldKey, field_type: this.fieldForm.field_type, is_required: this.fieldForm.is_required, is_active: this.fieldForm.is_active, translations: this.buildTranslationsArray() };
        let url = config.storeUrl;
        if (this.editingField) url = `${config.updateUrlTemplate}/${this.editingField.id}/update`;
        try {
          const response = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest", "X-CSRF-TOKEN": config.csrfToken, [config.csrfName]: config.csrfToken },
            body: JSON.stringify(payload)
          });
          const data = await response.json();
          if (data.ok) {
            const field = normalizeField(data.data);
            if (this.editingField) {
              const index = this.fields.findIndex((item) => String(item.id) === String(field.id));
              if (index >= 0) this.fields[index] = field;
            } else {
              this.fields.push(field);
            }
            this.closeModal();
            bootLucideIcons();
          } else {
            this.fieldError = data.messages && data.messages[0] || config.saveFieldFailedMessage || "Could not save the field.";
          }
        } catch (error) {
          this.fieldError = error.message || config.saveFieldFailedMessage || "Could not save the field.";
        }
      },
      async deleteField(field) {
        if (!confirm(config.confirmDeleteFieldMessage || "Delete this field?")) return;
        try {
          const response = await fetch(`${config.deleteUrlTemplate}/${field.id}/delete`, {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest", "X-CSRF-TOKEN": config.csrfToken, [config.csrfName]: config.csrfToken }
          });
          const data = await response.json();
          if (data.ok) {
            this.fields = this.fields.filter((item) => String(item.id) !== String(field.id));
          } else {
            alert(data.messages && data.messages[0] || config.deleteFailedMessage || "Could not delete the field.");
          }
        } catch (error) {
          alert(error.message);
        }
      },
      async onReorder(event) {
        const ordered = Array.from(event.target.querySelectorAll("[data-id]")).map((item) => parseInt(item.dataset.id, 10)).filter((id) => Number.isFinite(id));
        const sorted = [];
        ordered.forEach((id) => {
          const field = this.fields.find((item) => String(item.id) === String(id));
          if (field) sorted.push(field);
        });
        this.fields = sorted;
        await fetch(config.reorderUrl, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest", "X-CSRF-TOKEN": config.csrfToken, [config.csrfName]: config.csrfToken },
          body: JSON.stringify({ ordered_ids: ordered })
        });
      }
    };
  };

  // src/js/components/filePickerField.js
  var filePickerField = (config = {}) => ({
    fieldName: String(config.name || "file_id"),
    fileId: String(config.value || ""),
    fileInfo: { original_name: "", mime_type: "", category: "", is_image: false, url: "", human_size: "" },
    loading: false,
    _accept: String(config.accept || ""),
    _filterType: String(config.filterType || ""),
    init() {
      if (this.fileId !== "") this._loadFileInfo(this.fileId);
    },
    async _loadFileInfo(id) {
      if (!id) return;
      this.loading = true;
      const panel = document.getElementById("file-picker-panel");
      const baseUrl = panel?.dataset?.dataUrl ? String(panel.dataset.dataUrl).replace("/picker-data", "") : "/files";
      try {
        const resp = await fetch(`${baseUrl}/${encodeURIComponent(String(id))}/picker-info`, {
          credentials: "include",
          headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
        });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const payload = await resp.json();
        if (payload?.ok && isObject(payload?.data)) {
          const d = payload.data;
          this.fileInfo = {
            original_name: String(d.original_name || ""),
            mime_type: String(d.mime_type || ""),
            category: String(d.category || ""),
            is_image: Boolean(d.is_image),
            url: String(d.url || ""),
            human_size: String(d.human_size || "")
          };
        }
      } catch (err) {
        devError("[filePickerField] _loadFileInfo error:", err);
      } finally {
        this.loading = false;
      }
    },
    openPicker() {
      Alpine.store("filePicker").show({
        accept: this._accept,
        filterType: this._filterType,
        multi: false,
        onSelect: (file) => {
          this.fileId = String(file.id ?? "");
          this.fileInfo = {
            original_name: String(file.original_name || ""),
            mime_type: String(file.mime_type || ""),
            category: String(file.category || ""),
            is_image: Boolean(file.is_image),
            url: String(bestFilePreviewUrl(file)),
            human_size: String(file.human_size || "")
          };
        }
      });
    },
    clearFile() {
      this.fileId = "";
      this.fileInfo = { original_name: "", mime_type: "", category: "", is_image: false, url: "", human_size: "" };
    }
  });

  // src/js/components/translatableFileField.js
  var translatableFileField = (initialId = "", initialUrl = "", accept = "image") => ({
    fileId: String(initialId),
    fileUrl: resolveTranslatableFilePreviewUrl(initialId, initialUrl),
    previewUrl: resolveTranslatableFilePreviewUrl(initialId, initialUrl),
    accept: String(accept),
    pickerLabels: {
      image: { select: "Seleccionar imagen", change: "Cambiar imagen" },
      video: { select: "Seleccionar video", change: "Cambiar video" },
      document: { select: "Seleccionar documento", change: "Cambiar documento" },
      audio: { select: "Seleccionar audio", change: "Cambiar audio" },
      any: { select: "Seleccionar archivo", change: "Cambiar archivo" }
    },
    openPicker() {
      const filterTypeMap = { video: "video", document: "document", audio: "audio" };
      const filterType = filterTypeMap[this.accept] ?? "image";
      const mimeAccept = this.accept === "any" ? "" : this.accept.includes("/") ? this.accept : this.accept + "/*";
      Alpine.store("filePicker").show({
        filterType,
        accept: mimeAccept,
        multi: false,
        onSelect: (file) => {
          const fileId = String(file.id ?? "");
          const previewUrl = String(bestFilePreviewUrl(file) || file.url || "");
          this.applyFile(fileId, previewUrl);
        }
      });
    },
    applyFile(fileId = "", fileUrl = "") {
      const normalizedId = String(fileId || "");
      const normalizedUrl = resolveTranslatableFilePreviewUrl(normalizedId, fileUrl);
      this.fileId = normalizedId;
      this.fileUrl = normalizedUrl;
      this.previewUrl = normalizedUrl;
    },
    clearFile() {
      this.applyFile("", "");
    }
  });

  // src/js/components/blockRepeaterField.js
  var blockRepeaterField = (existingItems = [], itemFields = {}, fieldKey = "", langIdx = 0) => {
    const initItems = (existingItems || []).map((item) => {
      const out = {};
      Object.keys(itemFields || {}).forEach((subKey) => {
        if ((itemFields[subKey] || {}).type === "file") {
          out[subKey + "_file_id"] = String(item[subKey + "_file_id"] || "");
          out[subKey + "_preview_url"] = "";
          out[subKey + "_url"] = String(item[subKey + "_url"] || "");
        } else {
          out[subKey] = item[subKey] ?? "";
        }
      });
      return out;
    });
    return {
      items: initItems,
      itemFields: itemFields || {},
      fieldKey,
      langIdx,
      addItem() {
        const item = {};
        Object.keys(this.itemFields).forEach((subKey) => {
          if ((this.itemFields[subKey] || {}).type === "file") {
            item[subKey + "_file_id"] = "";
            item[subKey + "_preview_url"] = "";
            item[subKey + "_url"] = "";
          } else {
            item[subKey] = "";
          }
        });
        this.items.push(item);
      },
      removeItem(idx) {
        this.items.splice(idx, 1);
      },
      openPickerForItem(itemIdx, subKey, accept) {
        const filterTypeMap = { video: "video", document: "document", audio: "audio" };
        const filterType = filterTypeMap[accept] ?? "image";
        const mimeAccept = accept === "any" ? "" : accept.includes("/") ? accept : accept + "/*";
        Alpine.store("filePicker").show({
          filterType,
          accept: mimeAccept,
          multi: false,
          onSelect: (file) => {
            const fileId = String(file.id ?? "");
            const previewUrl = fileId ? `${window.location.origin}/files/${fileId}/view` : "";
            this.items[itemIdx][subKey + "_file_id"] = fileId;
            this.items[itemIdx][subKey + "_url"] = previewUrl;
            this.items[itemIdx][subKey + "_preview_url"] = previewUrl;
          }
        });
      }
    };
  };

  // src/js/components/adminMetadataField.js
  var adminMetadataField = (config = {}) => ({
    rows: Array.isArray(config.rows) && config.rows.length > 0 ? config.rows : [{ key: "", value: "" }],
    json: "{}",
    duplicates: [],
    init() {
      this.sync();
    },
    addRow() {
      this.rows.push({ key: "", value: "" });
      this.sync();
    },
    removeRow(index) {
      this.rows.splice(index, 1);
      if (this.rows.length === 0) {
        this.addRow();
        return;
      }
      this.sync();
    },
    importJson() {
      const raw = prompt("Paste JSON object here:");
      if (!raw) return;
      try {
        const parsed = JSON.parse(raw);
        if (typeof parsed !== "object" || parsed === null || Array.isArray(parsed)) {
          alert("Invalid JSON: Must be an object.");
          return;
        }
        const newRows = Object.entries(parsed).map(([key, value]) => ({
          key,
          value: typeof value === "object" ? JSON.stringify(value) : String(value)
        }));
        if (newRows.length > 0) {
          this.rows = newRows;
          this.sync();
        }
      } catch (e) {
        alert("Invalid JSON syntax: " + e.message);
      }
    },
    sync() {
      const metadata = {};
      const keys = [];
      this.duplicates = [];
      this.rows.forEach((row, index) => {
        const key = String(row.key || "").trim();
        if (key === "") return;
        if (keys.includes(key)) this.duplicates.push(index);
        keys.push(key);
        let val = String(row.value || "").trim();
        if (val === "true") val = true;
        else if (val === "false") val = false;
        else if (val === "null") val = null;
        else if (!isNaN(val) && val !== "") val = Number(val);
        metadata[key] = val;
      });
      this.json = JSON.stringify(metadata, null, 2);
    }
  });

  // src/js/components/adminMediaGallery.js
  var normalizePickerFile = (file) => {
    if (!file) return {};
    return {
      id: file.id ?? "",
      original_name: file.original_name ?? file.name ?? "",
      mime_type: file.mime_type ?? "",
      category: file.category ?? "",
      is_image: file.is_image ?? file.category === "image",
      url: file.url ?? "",
      human_size: file.human_size ?? "",
      variants: file.variants ?? {}
    };
  };
  var adminMediaGallery = (config = {}) => ({
    rows: Array.isArray(config.rows) ? config.rows : [],
    init() {
      if (this.rows.length === 0) this.addRow("cover");
      this.rows.forEach((row) => {
        if (!isObject(row.file)) row.file = {};
        if (row.hub_file_id) this.loadFileInfo(row);
      });
    },
    addRow(type = "gallery") {
      this.rows.push({ type, hub_file_id: "", external_url: "", alt_text: "", caption: "", sort_order: this.rows.length, is_active: true, file: {} });
    },
    removeRow(index) {
      this.rows.splice(index, 1);
    },
    chooseFile(row) {
      Alpine.store("filePicker").show({
        filterType: "image",
        accept: "image/*",
        multi: false,
        onSelect: (file) => {
          const selected = normalizePickerFile(file);
          row.hub_file_id = String(selected.id ?? "");
          row.external_url = "";
          row.file = {
            original_name: String(selected.original_name || ""),
            mime_type: String(selected.mime_type || ""),
            category: String(selected.category || ""),
            is_image: Boolean(selected.is_image),
            url: String(selected.url || ""),
            human_size: String(selected.human_size || ""),
            variants: selected.variants || {}
          };
        }
      });
    },
    clearFile(row) {
      row.hub_file_id = "";
      row.file = {};
    },
    fileName(row) {
      return String(row.file?.original_name || (row.hub_file_id ? `#${row.hub_file_id}` : ""));
    },
    async loadFileInfo(row) {
      const panel = document.getElementById("file-picker-panel");
      const baseUrl = panel?.dataset?.dataUrl ? String(panel.dataset.dataUrl).replace("/picker-data", "") : "/files";
      try {
        const resp = await fetch(`${baseUrl}/${encodeURIComponent(String(row.hub_file_id))}/picker-info`, {
          credentials: "include",
          headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
        });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const payload = await resp.json();
        if (payload?.ok && isObject(payload?.data)) {
          const d = normalizePickerFile(payload.data);
          row.file = {
            original_name: String(d.original_name || ""),
            mime_type: String(d.mime_type || ""),
            category: String(d.category || ""),
            is_image: Boolean(d.is_image),
            url: String(d.url || ""),
            human_size: String(d.human_size || ""),
            variants: d.variants || {}
          };
        }
      } catch (err) {
        devError("[adminMediaGallery] loadFileInfo error:", err);
      }
    }
  });

  // src/js/components/langTabs.js
  var langTabs = (defaultId = 0, translateUrl = "", sourceLangCode = "EN") => ({
    active: defaultId,
    translating: false,
    translateError: "",
    translatingAll: false,
    translateAllProgress: "",
    isActive(id) {
      return this.active === id;
    },
    setTab(id) {
      this.active = id;
    },
    async _translatePairs(targetLangCode, fieldPairs) {
      for (const pair of fieldPairs) {
        const sourceEl = document.querySelector(pair.from);
        const targetEl = document.querySelector(pair.to);
        if (!(sourceEl instanceof HTMLInputElement || sourceEl instanceof HTMLTextAreaElement)) continue;
        if (!(targetEl instanceof HTMLInputElement || targetEl instanceof HTMLTextAreaElement)) continue;
        const sourceText = sourceEl.value.trim();
        if (sourceText === "") continue;
        const url = new URL(translateUrl, window.location.origin);
        url.searchParams.set("text", sourceText);
        url.searchParams.set("source_lang", sourceLangCode.toUpperCase());
        url.searchParams.set("target_lang", targetLangCode.toUpperCase());
        const res = await fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin" });
        const json = await res.json();
        if (json && typeof json.translated === "string") {
          targetEl.value = json.translated;
          targetEl.dispatchEvent(new Event("input", { bubbles: true }));
        } else if (json && json.error) {
          throw new Error(json.error);
        }
      }
    },
    async autoTranslate(targetLangCode, fieldPairs) {
      if (translateUrl === "" || this.translating || this.translatingAll) return;
      this.translating = true;
      this.translateError = "";
      try {
        await this._translatePairs(targetLangCode, fieldPairs);
      } catch (e) {
        this.translateError = e instanceof Error ? e.message : String(e);
      } finally {
        this.translating = false;
      }
    },
    async autoTranslateAll(targets) {
      if (translateUrl === "" || this.translating || this.translatingAll) return;
      this.translatingAll = true;
      this.translateError = "";
      try {
        for (let i = 0; i < targets.length; i++) {
          const { langCode, fieldPairs } = targets[i];
          this.translateAllProgress = langCode + " (" + (i + 1) + "/" + targets.length + ")";
          await this._translatePairs(langCode, fieldPairs);
        }
        this.translateAllProgress = "";
      } catch (e) {
        this.translateError = e instanceof Error ? e.message : String(e);
        this.translateAllProgress = "";
      } finally {
        this.translatingAll = false;
      }
    },
    copyFieldToAll(sourceSelector, targetSelectors) {
      const sourceEl = document.querySelector(sourceSelector);
      if (!(sourceEl instanceof HTMLInputElement || sourceEl instanceof HTMLTextAreaElement)) return;
      const sourceValue = sourceEl.value;
      for (const targetSelector of targetSelectors) {
        const targetEl = document.querySelector(targetSelector);
        if (targetEl instanceof HTMLInputElement || targetEl instanceof HTMLTextAreaElement) {
          targetEl.value = sourceValue;
          targetEl.dispatchEvent(new Event("input", { bubbles: true }));
        }
      }
    },
    copyFileFieldToAll(sourceFileIdSelector, sourceFileUrlSelector, fieldKeyPattern) {
      const allFileIdInputs = document.querySelectorAll(`input[name*="[block_data][${fieldKeyPattern}_file_id]"]`);
      const allFileUrlInputs = document.querySelectorAll(`input[name*="[block_data][${fieldKeyPattern}_url]"]`);
      this.copyFileFieldToTargets(sourceFileIdSelector, sourceFileUrlSelector, allFileIdInputs, allFileUrlInputs);
    },
    copyFileFieldToTargets(sourceFileIdSelector, sourceFileUrlSelector, targetFileIdSelectors, targetFileUrlSelectors) {
      const sourceFileId = document.querySelector(sourceFileIdSelector);
      const sourceFileUrl = document.querySelector(sourceFileUrlSelector);
      if (!sourceFileId || !sourceFileUrl) {
        console.warn("[langTabs] Could not find source file elements");
        return;
      }
      const sourceFileIdValue = String(sourceFileId.value || "");
      const sourceFileUrlValue = String(sourceFileUrl.value || "");
      const resolvedFileUrl = resolveTranslatableFilePreviewUrl(sourceFileIdValue, sourceFileUrlValue);
      const allFileIdInputs = Array.from(
        targetFileIdSelectors,
        (selector) => typeof selector === "string" ? document.querySelector(selector) : selector
      ).filter((input) => input instanceof HTMLInputElement);
      const allFileUrlInputs = Array.from(
        targetFileUrlSelectors,
        (selector) => typeof selector === "string" ? document.querySelector(selector) : selector
      ).filter((input) => input instanceof HTMLInputElement);
      const updatedComponents = /* @__PURE__ */ new Set();
      allFileIdInputs.forEach((input) => {
        if (input.value === sourceFileIdValue) return;
        input.value = sourceFileIdValue;
        input.dispatchEvent(new Event("input", { bubbles: true }));
        const fileFieldContainer = input.closest('[x-data*="translatableFileField"]');
        const componentData = fileFieldContainer?._x_dataStack?.[0];
        if (componentData && typeof componentData.applyFile === "function") updatedComponents.add(componentData);
      });
      allFileUrlInputs.forEach((input) => {
        if (input.value === resolvedFileUrl) return;
        input.value = resolvedFileUrl;
        input.dispatchEvent(new Event("input", { bubbles: true }));
        const fileFieldContainer = input.closest('[x-data*="translatableFileField"]');
        const componentData = fileFieldContainer?._x_dataStack?.[0];
        if (componentData && typeof componentData.applyFile === "function") updatedComponents.add(componentData);
      });
      updatedComponents.forEach((componentData) => {
        componentData.applyFile(sourceFileIdValue, resolvedFileUrl);
      });
    }
  });

  // src/js/components/jsonEditor.js
  var jsonEditor = (initialValue = "{}") => ({
    value: initialValue,
    isValid: true,
    errorMsg: "",
    validate() {
      try {
        JSON.parse(this.value);
        this.isValid = true;
        this.errorMsg = "";
      } catch (e) {
        this.isValid = false;
        this.errorMsg = e instanceof Error ? e.message : String(e);
      }
    },
    format() {
      try {
        this.value = JSON.stringify(JSON.parse(this.value), null, 2);
        this.isValid = true;
        this.errorMsg = "";
      } catch (e) {
        this.isValid = false;
        this.errorMsg = e instanceof Error ? e.message : String(e);
      }
    }
  });

  // src/js/components/blockPreview.js
  var blockPreview = () => ({
    isOpen: false,
    loading: false,
    error: "",
    html: "",
    blockKey: "",
    openWithEvent(event) {
      const { blockKey, blockConfig, blockData } = event.detail || {};
      this.open(blockKey || "", blockConfig || {}, blockData || {});
    },
    open(blockKey, blockConfig, blockData) {
      this.isOpen = true;
      this.loading = true;
      this.error = "";
      this.html = "";
      this.blockKey = blockKey;
      const previewUrl = document.querySelector('meta[name="block-preview-url"]')?.getAttribute("content") || "/admin/cms/blocks/preview";
      const csrfInput = document.querySelector('input[name^="ci4_"][name$="_csrf_token"]') || document.querySelector('input[name="csrf_token"]');
      const csrfName = csrfInput?.name || "csrf_token";
      const csrfToken = csrfInput?.value || "";
      const body = new URLSearchParams({
        block_key: blockKey,
        block_config: JSON.stringify(blockConfig),
        block_data: JSON.stringify(blockData),
        [csrfName]: csrfToken
      });
      fetch(previewUrl, { method: "POST", headers: { "X-Requested-With": "XMLHttpRequest" }, body }).then((r) => r.json()).then((json) => {
        this.html = json.html || "";
        this.loading = false;
      }).catch((err) => {
        this.error = "Error al cargar el preview: " + (err instanceof Error ? err.message : String(err));
        this.loading = false;
      });
    },
    close() {
      this.isOpen = false;
    }
  });

  // src/js/components/blockTypeDesigner.js
  var blockTypeDesigner = (templates = [], initialSchema = null) => ({
    templates,
    selectedTemplate: null,
    customMode: false,
    customBlockKey: "",
    schemaFields: [],
    configFields: [],
    schemaJson: "{}",
    isContainer: false,
    allowedChildren: [],
    get effectiveBlockKey() {
      return this.customMode ? this.customBlockKey : this.selectedTemplate?.key || "";
    },
    init() {
      if (initialSchema) this.loadFromSchema(initialSchema);
    },
    selectTemplate(template) {
      this.selectedTemplate = template;
      this.customMode = false;
      const schema = template.default_schema || {};
      this.schemaFields = this._schemaToRows(schema.fields || {});
      this.configFields = this._schemaToRows(schema.config_fields || {});
      this.isContainer = false;
      this.allowedChildren = schema.allowed_children || [];
      this.rebuildJson();
    },
    enableCustomMode() {
      this.selectedTemplate = null;
      this.customMode = true;
      this.schemaFields = [];
      this.configFields = [];
      this.isContainer = false;
      this.allowedChildren = [];
      this.rebuildJson();
    },
    loadFromSchema(schema) {
      this.schemaFields = this._schemaToRows(schema.fields || {});
      this.configFields = this._schemaToRows(schema.config_fields || {});
      this.allowedChildren = schema.allowed_children || [];
      this.rebuildJson();
    },
    _schemaToRows(fieldsObj) {
      return Object.entries(fieldsObj).map(([key, def]) => ({
        key,
        type: def.type || "string",
        label: def.label || key,
        required: def.required === true || def.required === 1,
        options: Array.isArray(def.options) ? def.options.join(", ") : "",
        default: def.default || "",
        item_fields: def.item_fields ? this._schemaToRows(def.item_fields) : []
      }));
    },
    addField(section, parentRow = null) {
      const row = { key: "", type: "string", label: "", required: false, options: "", default: "", item_fields: [] };
      if (parentRow) {
        if (!parentRow.item_fields) parentRow.item_fields = [];
        parentRow.item_fields.push(row);
      } else if (section === "config") {
        this.configFields.push(row);
      } else {
        this.schemaFields.push(row);
      }
      this.rebuildJson();
    },
    removeField(section, index, parentRow = null) {
      if (parentRow) {
        parentRow.item_fields.splice(index, 1);
      } else if (section === "config") {
        this.configFields.splice(index, 1);
      } else {
        this.schemaFields.splice(index, 1);
      }
      this.rebuildJson();
    },
    rebuildJson() {
      const buildObj = (rows) => {
        const obj = {};
        for (const row of rows) {
          if (!row.key) continue;
          const def = { type: row.type, label: row.label, required: row.required };
          if (row.type === "select" && row.options) def.options = row.options.split(",").map((s) => s.trim()).filter(Boolean);
          if (row.type === "repeater") def.item_fields = buildObj(row.item_fields || []);
          if (row.default !== "") def.default = row.default;
          obj[row.key] = def;
        }
        return obj;
      };
      const schema = { fields: buildObj(this.schemaFields), config_fields: buildObj(this.configFields) };
      if (this.isContainer) schema.allowed_children = this.allowedChildren || [];
      this.schemaJson = JSON.stringify(schema, null, 2);
    },
    openPreview() {
      const sampleData = this.selectedTemplate?.preview_sample || {};
      const sampleConfig = this.selectedTemplate?.config_sample || {};
      const key = this.selectedTemplate?.key || document.getElementById("block_key")?.value || "";
      window.dispatchEvent(new CustomEvent("block-preview-open", {
        detail: { blockKey: key, blockConfig: sampleConfig, blockData: sampleData }
      }));
    },
    isSelected(template) {
      return this.selectedTemplate?.key === template.key;
    }
  });

  // src/js/components/schemaEditor.js
  var schemaEditor = (initialSchema = {}, initialIsContainer = false) => ({
    schemaFields: [],
    configFields: [],
    schemaJson: "{}",
    isContainer: initialIsContainer,
    allowedChildren: [],
    init() {
      this.schemaFields = this._schemaToRows(initialSchema.fields || {});
      this.configFields = this._schemaToRows(initialSchema.config_fields || {});
      this.allowedChildren = initialSchema.allowed_children || [];
      this.rebuildJson();
    },
    _schemaToRows(fieldsObj) {
      return Object.entries(fieldsObj).map(([key, def]) => ({
        key,
        type: def.type || "string",
        label: def.label || key,
        required: def.required === true || def.required === 1,
        options: Array.isArray(def.options) ? def.options.join(", ") : "",
        default: def.default || "",
        item_fields: def.item_fields ? this._schemaToRows(def.item_fields) : []
      }));
    },
    addField(section, parentRow = null) {
      const row = { key: "", type: "string", label: "", required: false, options: "", default: "", item_fields: [] };
      if (parentRow) {
        if (!parentRow.item_fields) parentRow.item_fields = [];
        parentRow.item_fields.push(row);
      } else if (section === "config") {
        this.configFields.push(row);
      } else {
        this.schemaFields.push(row);
      }
      this.rebuildJson();
    },
    removeField(section, index, parentRow = null) {
      if (parentRow) {
        parentRow.item_fields.splice(index, 1);
      } else if (section === "config") {
        this.configFields.splice(index, 1);
      } else {
        this.schemaFields.splice(index, 1);
      }
      this.rebuildJson();
    },
    rebuildJson() {
      const buildObj = (rows) => {
        const obj = {};
        for (const row of rows) {
          if (!row.key) continue;
          const def = { type: row.type, label: row.label, required: row.required };
          if (row.type === "select" && row.options) def.options = row.options.split(",").map((s) => s.trim()).filter(Boolean);
          if (row.type === "repeater") def.item_fields = buildObj(row.item_fields || []);
          if (row.default !== "") def.default = row.default;
          obj[row.key] = def;
        }
        return obj;
      };
      const schema = { fields: buildObj(this.schemaFields), config_fields: buildObj(this.configFields) };
      if (this.isContainer) schema.allowed_children = this.allowedChildren || [];
      this.schemaJson = JSON.stringify(schema, null, 2);
    }
  });

  // src/js/components/blockSorter.js
  var blockSorter = (reorderUrl = "") => ({
    saving: false,
    saved: false,
    dirty: false,
    _list: null,
    _sortable: null,
    _saveTimeoutId: null,
    _abortController: null,
    init() {
      if (!reorderUrl || typeof Sortable === "undefined") {
        devError("[blockSorter] Missing reorderUrl or Sortable library");
        return;
      }
      const list = this.$el.querySelector("[data-sortable-list]") || this.$el.querySelector("#block-sortable-list");
      if (!list) {
        devError("[blockSorter] Sortable list element not found");
        return;
      }
      this._list = list;
      this._abortController = new AbortController();
      this._cleanupSortable();
      try {
        this._sortable = Sortable.create(list, {
          handle: "[data-drag-handle]",
          animation: 150,
          ghostClass: "opacity-40",
          onEnd: () => {
            this.dirty = true;
            this.saved = false;
          }
        });
      } catch (err) {
        devError("[blockSorter] Failed to create Sortable instance:", err);
      }
      this._registerMutationObserver();
    },
    _registerMutationObserver() {
      if (typeof MutationObserver === "undefined") return;
      const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
          for (const node of mutation.removedNodes) {
            if (node === this.$el || this.$el.contains?.(node)) {
              this.destroy();
              observer.disconnect();
              return;
            }
          }
        }
      });
      observer.observe(this.$el.parentNode || document.body, { childList: true, subtree: true });
    },
    _cleanupSortable() {
      if (this._sortable && typeof this._sortable.destroy === "function") {
        try {
          this._sortable.destroy();
        } catch (err) {
          devError("[blockSorter] Error destroying Sortable:", err);
        }
      }
      this._sortable = null;
    },
    destroy() {
      if (this._saveTimeoutId) {
        clearTimeout(this._saveTimeoutId);
        this._saveTimeoutId = null;
      }
      if (this._abortController) {
        this._abortController.abort();
        this._abortController = null;
      }
      this._cleanupSortable();
      this._list = null;
      this.saving = false;
      this.saved = false;
      this.dirty = false;
    },
    saveOrder() {
      if (!this._list) {
        devError("[blockSorter] List reference is null");
        return;
      }
      if (!reorderUrl) {
        devError("[blockSorter] Reorder URL is not configured");
        return;
      }
      const items = this._list.querySelectorAll("[data-block-id], [data-id]");
      if (items.length === 0) {
        devError("[blockSorter] No items found to reorder");
        return;
      }
      const orders = {};
      items.forEach((el, index) => {
        const id = el.getAttribute("data-block-id") || el.getAttribute("data-id");
        if (id) orders[id] = index + 1;
      });
      if (Object.keys(orders).length === 0) {
        devError("[blockSorter] No valid item IDs found");
        return;
      }
      this.saving = true;
      this.saved = false;
      if (this._saveTimeoutId) {
        clearTimeout(this._saveTimeoutId);
        this._saveTimeoutId = null;
      }
      const csrfInput = document.querySelector('input[name^="ci4_"][name$="_csrf_token"]') || document.querySelector('input[name="csrf_token"]');
      const csrfName = csrfInput?.name || "csrf_token";
      const csrfToken = csrfInput?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
      const body = new URLSearchParams({ [csrfName]: csrfToken });
      Object.entries(orders).forEach(([id, pos]) => body.append(`orders[${id}]`, String(pos)));
      fetch(reorderUrl, { method: "POST", headers: { "X-Requested-With": "XMLHttpRequest" }, body, signal: this._abortController?.signal }).then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      }).then(() => {
        if (this.saving) {
          this.saving = false;
          this.saved = true;
          this.dirty = false;
          this._saveTimeoutId = setTimeout(() => {
            this.saved = false;
          }, 2500);
        }
      }).catch((err) => {
        if (err.name === "AbortError") return;
        devError("[blockSorter] Save order error:", err);
        if (this.saving) {
          this.saving = false;
          window.dispatchEvent(new CustomEvent("toast", { detail: { type: "error", message: "Error al guardar el orden" } }));
        }
      });
    }
  });

  // src/js/components/sessionWatcher.js
  var bootSessionExpiryWatcher = () => {
    const meta = document.querySelector('meta[name="session-expires-at"]');
    if (!(meta instanceof HTMLMetaElement)) return;
    const expiresAt = parseInt(meta.getAttribute("content") || "0", 10);
    if (!Number.isFinite(expiresAt) || expiresAt <= 0) return;
    const WARN_BEFORE_SECONDS = 60;
    let warned = false;
    const tick = () => {
      const remaining = expiresAt - Math.floor(Date.now() / 1e3);
      if (!warned && remaining > 0 && remaining <= WARN_BEFORE_SECONDS) {
        warned = true;
        console.warn(`[session] Token expires in ~${remaining}s. Save your work.`);
        window.dispatchEvent(new CustomEvent("session:expiring-soon", { detail: { remainingSeconds: remaining } }));
      }
      if (remaining <= 0) {
        window.dispatchEvent(new CustomEvent("session:expired"));
        clearInterval(handle);
      }
    };
    const handle = setInterval(tick, 5e3);
    tick();
  };

  // src/js/components/googleAuth.js
  var handleGoogleCredentialResponse = (response) => {
    const token = response && typeof response.credential === "string" ? response.credential : "";
    if (token === "") {
      devError("[Google Auth] Empty credential in response");
      return;
    }
    const tokenInput = document.getElementById("google-id-token");
    const loginForm = document.getElementById("google-login-form");
    if (!(tokenInput instanceof HTMLInputElement) || !(loginForm instanceof HTMLFormElement)) {
      devError("[Google Auth] Required form elements not found in DOM");
      return;
    }
    window.dispatchEvent(new CustomEvent("login:loading", { detail: { flow: "google" } }));
    tokenInput.value = token;
    loginForm.submit();
  };

  // src/js/components/richTextEditor.js
  var richTextEditor = function richTextEditor2(initialContent, fieldName) {
    let editor = null;
    let toolbar = null;
    let onToolbarClick = null;
    const syncHiddenInput = (component, value) => {
      const input = component.$refs.hiddenInput;
      if (input instanceof HTMLInputElement) input.value = value;
    };
    const teardown = (component) => {
      if (toolbar && onToolbarClick) toolbar.removeEventListener("click", onToolbarClick);
      toolbar = null;
      onToolbarClick = null;
      editor?.destroy();
      editor = null;
      if (component?.$refs?.editorEl instanceof HTMLElement) component.$refs.editorEl.innerHTML = "";
    };
    const runCommand = (action, value) => {
      if (!editor) return;
      const chain = editor.chain().focus();
      switch (action) {
        case "bold":
          chain.toggleBold();
          break;
        case "italic":
          chain.toggleItalic();
          break;
        case "strike":
          chain.toggleStrike();
          break;
        case "code":
          chain.toggleCode();
          break;
        case "heading":
          chain.toggleHeading({ level: Number.parseInt(value || "0", 10) });
          break;
        case "bulletList":
          chain.toggleBulletList();
          break;
        case "orderedList":
          chain.toggleOrderedList();
          break;
        case "blockquote":
          chain.toggleBlockquote();
          break;
        case "hr":
          chain.setHorizontalRule();
          break;
        case "undo":
          chain.undo();
          break;
        case "redo":
          chain.redo();
          break;
        case "link": {
          const currentHref = editor.getAttributes("link").href || "";
          const url = window.prompt("URL del enlace:", currentHref);
          if (url === null) return;
          if (url === "") chain.extendMarkRange("link").unsetLink();
          else chain.extendMarkRange("link").setLink({ href: url });
          break;
        }
        default:
          return;
      }
      chain.run();
    };
    return {
      inputName: fieldName || "",
      init() {
        if (typeof window.tiptap === "undefined") return;
        const editorHost = this.$refs.editorEl;
        if (!(editorHost instanceof HTMLElement)) return;
        teardown(this);
        editorHost.innerHTML = "";
        const { Editor, StarterKit, Placeholder } = window.tiptap;
        editor = new Editor({
          element: editorHost,
          extensions: [
            StarterKit.configure({ link: { openOnClick: false, autolink: true } }),
            Placeholder.configure({ placeholder: "Escribe aqu\xED\u2026" })
          ],
          content: initialContent || "",
          onUpdate: ({ editor: currentEditor }) => {
            syncHiddenInput(this, currentEditor.getHTML());
          }
        });
        syncHiddenInput(this, editor.getHTML());
        toolbar = this.$el.querySelector("[data-richtext-toolbar]");
        if (toolbar instanceof HTMLElement) {
          onToolbarClick = (event) => {
            const target = event.target;
            const button = target && typeof target.closest === "function" ? target.closest("[data-richtext-action]") : null;
            if (!(button instanceof HTMLElement) || !toolbar.contains(button)) return;
            event.preventDefault();
            runCommand(button.dataset.richtextAction || "", button.dataset.richtextLevel || button.dataset.richtextValue || "");
          };
          toolbar.addEventListener("click", onToolbarClick);
        }
      },
      destroy() {
        teardown(this);
      },
      isActive(type, attrs) {
        return editor?.isActive(type, attrs) ?? false;
      }
    };
  };

  // src/js/app.js
  document.addEventListener("alpine:init", () => {
    Alpine.store("confirm", confirmStore());
    Alpine.store("toast", toastStore);
    Alpine.store("filePicker", filePickerStore);
    Alpine.data("appShell", appShell);
    Alpine.data("remoteTable", remoteTableFactory);
    Alpine.data("formFieldBuilder", formFieldBuilderFactory);
    Alpine.data("filePickerField", filePickerField);
    Alpine.data("translatableFileField", translatableFileField);
    Alpine.data("blockRepeaterField", blockRepeaterField);
    Alpine.data("adminMetadataField", adminMetadataField);
    Alpine.data("adminMediaGallery", adminMediaGallery);
    Alpine.data("langTabs", langTabs);
    Alpine.data("jsonEditor", jsonEditor);
    Alpine.data("blockPreview", blockPreview);
    Alpine.data("blockTypeDesigner", blockTypeDesigner);
    Alpine.data("schemaEditor", schemaEditor);
    Alpine.data("blockSorter", blockSorter);
    Alpine.data("catalogMetadataField", (config = {}) => Alpine.data("adminMetadataField")(config));
    Alpine.data("catalogItemMedia", (config = {}) => Alpine.data("adminMediaGallery")(config));
    window.remoteTable = remoteTableFactory;
    window.formFieldBuilder = formFieldBuilderFactory;
    window.confirmDeleteMessage = buildConfirmDeleteMessage;
    window.bestFilePreviewUrl = bestFilePreviewUrl;
    window.resolveTranslatableFilePreviewUrl = resolveTranslatableFilePreviewUrl;
  });
  window.handleGoogleCredentialResponse = handleGoogleCredentialResponse;
  window.richTextEditor = richTextEditor;
  var lucideBootstrapped = false;
  document.addEventListener("DOMContentLoaded", () => {
    if (!lucideBootstrapped) {
      bootLucideIcons();
      lucideBootstrapped = true;
    }
    bootSlugFields();
    bootSessionExpiryWatcher();
  });
  window.addEventListener("load", () => {
    if (!lucideBootstrapped) {
      bootLucideIcons();
      lucideBootstrapped = true;
    }
  });
})();
