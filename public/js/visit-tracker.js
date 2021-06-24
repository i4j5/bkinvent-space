/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/visitor.js":
/*!*********************************!*\
  !*** ./resources/js/visitor.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ init)
/* harmony export */ });
function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

var url, select, mask, track_time, default_number;
var prefix = '__trk_';
var data = {
  visit: 0,
  first_visit: 0,
  google_client_id: '',
  metrika_client_id: '',
  amocrm_visitor_uid: '',
  landing_page: '',
  referrer: '',
  phone: {
    number: '',
    ttl: ''
  },
  utm: {
    utm_source: '',
    utm_medium: '',
    utm_campaign: '',
    utm_content: '',
    utm_term: '',
    utm_referrer: ''
  }
};
/**
 *  Init
 * @param {object} options 
 */

function init(options) {
  url = options.url;
  select = options.select;
  mask = options.mask;
  default_number = options.default_number;
  track_time = options.track_time * 1000;
  var search = window.location.search;
  data.first_visit = getLocalStorage("".concat(prefix, "first_visit")) || 0;
  data.visit = getCookie("".concat(prefix, "visit")) || 0;

  if (data.visit == 0) {
    setLocalStorage("".concat(prefix, "referrer"), '');
    setLocalStorage("".concat(prefix, "landing_page"), '');
    setLocalStorage("".concat(prefix, "utm"), '');
  } else {
    setCookie("".concat(prefix, "visit"), data.visit, track_time);
  }

  if (document.referrer && document.referrer.split('/')[2] != window.location.hostname) {
    data.referrer = setLocalStorage("".concat(prefix, "referrer"), document.referrer);
    setLocalStorage("".concat(prefix, "utm"), '');
    data.landing_page = setLocalStorage("".concat(prefix, "landing_page"), document.location.hostname + document.location.pathname);
    data.visit = 0;
  } else {
    data.referrer = getLocalStorage("".concat(prefix, "referrer"));
    data.landing_page = getLocalStorage("".concat(prefix, "landing_page")) || setLocalStorage("".concat(prefix, "landing_page"), document.location.hostname + document.location.pathname);
  }

  var google_client_id = getCookie('_ga');
  var metrika_client_id = getCookie('_ym_uid');
  var amocrm_visitor_uid = getLocalStorage('amocrm_visitor_uid');

  if (google_client_id) {
    data.google_client_id = google_client_id.split('.').slice(-2).join('.');
  }

  if (metrika_client_id) {
    data.metrika_client_id = metrika_client_id;
  }

  if (amocrm_visitor_uid) {
    data.amocrm_visitor_uid = amocrm_visitor_uid;
  }

  if (search.match(/utm_source=/)) {
    var old_utm = getLocalStorage("".concat(prefix, "utm")) ? JSON.parse(getLocalStorage("".concat(prefix, "utm"))) : {};
    data.utm.utm_source = search.split('utm_source=')[1].split('&')[0];
    if (data.visit && data.utm.utm_source != old_utm.utm_source) data.visit = 0; // ???
    // TODO: Проверка всех значений 

    if (search.match(/utm_medium=/)) data.utm.utm_medium = search.split('utm_medium=')[1].split('&')[0];
    if (search.match(/utm_campaign=/)) data.utm.utm_campaign = search.split('utm_campaign=')[1].split('&')[0];
    if (search.match(/utm_content=/)) data.utm.utm_content = search.split('utm_content=')[1].split('&')[0];
    if (search.match(/utm_term=/)) data.utm.utm_term = decodeURI(search.split('utm_term=')[1].split('&')[0]);
    if (search.match(/utm_referrer=/)) data.utm.utm_referrer = search.split('utm_referrer=')[1].split('&')[0];
    setLocalStorage("".concat(prefix, "utm"), JSON.stringify(data.utm));
  } else {
    var utm = getLocalStorage("".concat(prefix, "utm"));

    if (utm) {
      data.utm = JSON.parse(utm);
    }
  }

  if (data.visit) {
    intervalCheck();
  } else {
    sendCreate();
  }

  return {
    substitutionNumber: substitutionNumber,
    getCookie: getCookie,
    setCookie: setCookie,
    getLocalStorage: getLocalStorage,
    setLocalStorage: setLocalStorage,
    getData: getData,
    sendUpdate: sendUpdate,
    sendCreate: sendCreate
  };
}
/**
 * Замена телефона
 * @param {string} phone 
 */

function substitutionNumber() {
  var number = data.phone.number.replace(/\s{2,}/g, '').substring(0).replace(/(\d)(\d\d\d)(\d\d\d)(\d\d)(\d\d)/, mask);
  var elements = document.querySelectorAll(select);

  var _iterator = _createForOfIteratorHelper(elements),
      _step;

  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var elem = _step.value;
      elem.innerHTML = number;

      if (elem.tagName === 'A') {
        elem.setAttribute('href', 'tel:+' + data.phone.number);
      }
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
}

function checkPhoneTTL() {
  if (data.phone.ttl) {
    if (Date.parse(data.phone.ttl) <= Date.now()) {
      data.phone.number = default_number;
      data.phone.ttl = false;
      substitutionNumber();
    }
  }
}

function getCookie(name) {
  var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}

function setCookie(name, value, time) {
  var str = name + '=' + value;

  if (data) {
    str = str + '; expires=' + new Date(Date.now() + time).toUTCString();
  }

  document.cookie = str;
  return value;
}
/**
 * Чтение из локального хранилища
 * @param {string} name 
 * @returns {string}
 */


function getLocalStorage(name) {
  return localStorage.getItem(name);
}
/**
 * Запись в локальное хранилище
 * @param {string} name 
 * @param {string} value 
 * @returns {string} 
 */


function setLocalStorage(name, value) {
  localStorage.setItem(name, value);
  return value;
}
/**
 * Получить объект данных 
 * @returns {object}
 */


function getData() {
  return data;
}
/**
 * Получение асинхронных Данных
 * @returns {bool}
 */


function getAsynсData() {
  if (data.metrika_client_id && data.google_client_id && data.amocrm_visitor_uid) {
    return true;
  }

  if (!data.amocrm_visitor_uid) {
    data.amocrm_visitor_uid = getLocalStorage('amocrm_visitor_uid') || '';
  }

  if (!data.google_client_id) {
    var google_client_id = getCookie('_ga');

    if (google_client_id) {
      data.google_client_id = google_client_id.split('.').slice(-2).join('.');
    }
  }

  if (!data.metrika_client_id) {
    var metrika_client_id = getCookie('_ym_uid');

    if (metrika_client_id) {
      data.metrika_client_id = metrika_client_id;
    }
  }

  return false;
}
/**
 * Проверка данных
 */


function intervalCheck() {
  var checks = 0;
  var interval = 50;
  var maxTimeout = 3000;
  var maxChecks = maxTimeout / interval,
      t = setInterval(function () {
    if (getAsynсData() || ++checks > maxChecks) {
      sendUpdate();
      clearInterval(t);
    }
  }, interval);
}
/**
 * Отправка обновлений о визите
 */


function sendUpdate() {
  sendRequest(url + '/update', function (res) {
    if (data.phone != false) {
      data.phone = res.data.phone;
    }

    if (data.phone) {
      substitutionNumber();

      if (data.phone.ttl) {
        setTimeout(checkPhoneTTL, Date.parse(data.phone.ttl) - Date.now());
      }
    }
  });
}
/** 
 * Отправка данных для создание визита
*/


function sendCreate() {
  sendRequest(url + '/create', function (res) {
    data.visit = res.data.visit;
    data.first_visit = res.data.first_visit;
    setCookie("".concat(prefix, "visit"), res.data.visit, track_time);
    setLocalStorage("".concat(prefix, "first_visit"), res.data.first_visit);
    intervalCheck();

    if (data.phone != false) {
      data.phone = res.data.phone;
    }

    if (data.phone) {
      substitutionNumber();
    }
  });
}
/**
 * Отправка данных на сервер
 */


function sendRequest(url, callback) {
  var xhr = new XMLHttpRequest();

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200 && callback && typeof callback == "function") {
      try {
        var res = JSON.parse(xhr.responseText);
        callback(res);
      } catch (e) {}
    }
  };

  xhr.open('POST', url, true);
  xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');
  xhr.send(JSON.stringify(data));
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		if(__webpack_module_cache__[moduleId]) {
/******/ 			return __webpack_module_cache__[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!***************************************!*\
  !*** ./resources/js/visit-tracker.js ***!
  \***************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _visitor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./visitor */ "./resources/js/visitor.js");

var visit = (0,_visitor__WEBPACK_IMPORTED_MODULE_0__.default)({
  default_number: "78633090658",
  url: "http://localhost:8000" + '/api/site/visitor',
  select: ".".concat("dynamic-phone"),
  mask: "8 ($2) $3-$4-$5",
  track_time: "10"
});
window.VISIT = visit;
})();

/******/ })()
;