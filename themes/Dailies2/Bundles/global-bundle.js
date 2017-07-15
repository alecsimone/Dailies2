/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 207);
/******/ })
/************************************************************************/
/******/ ({

/***/ 207:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(208);
__webpack_require__(209);
__webpack_require__(210);
__webpack_require__(211);
__webpack_require__(212);
__webpack_require__(213);

/***/ }),

/***/ 208:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 209:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 210:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 211:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 212:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 213:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


jQuery(document).mouseup(function (e) {
	var searchBox = jQuery('#searchbox');
	if (jQuery('#searchToggle').is(e.target)) {
		toggleSearch();
	} else if (!searchBox.is(e.target) && searchBox.has(e.target).length === 0) {
		searchBox.css("maxWidth", "0");
	}
});

function toggleSearch() {
	var searchBox = jQuery("#searchbox");
	if (searchBox.width() > 0) {
		searchBox.css("maxWidth", "0");
	} else {
		searchBox.css("maxWidth", "300px");
	}
}

jQuery("body").on('mouseenter', '.hoverReplacer', function () {
	replaceImage(jQuery(this));
});
jQuery("body").on('mouseleave', '.hoverReplacer', function () {
	if (!jQuery(this).hasClass('replaceHold')) {
		replaceImage(jQuery(this));
	} else {
		jQuery(this).removeClass('replaceHold');
	}
});

function replaceImage(thisIMG) {
	var thisOldSrc = thisIMG.attr("src");
	var thisNewSrc = thisIMG.attr("data-replace-src");
	thisIMG.attr("src", thisNewSrc);
	thisIMG.attr("data-replace-src", thisOldSrc);
}

/***/ })

/******/ });
//# sourceMappingURL=global-bundle.js.map