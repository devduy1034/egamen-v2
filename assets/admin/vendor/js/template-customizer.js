! function(t, e) {
    if ("object" == typeof exports && "object" == typeof module) module.exports = e();
    else if ("function" == typeof define && define.amd) define([], e);
    else {
        var i = e();
        for (var o in i)("object" == typeof exports ? exports : t)[o] = i[o]
    }
}(self, (function() {
    return function() {
        "use strict";
        var t = {
                7621: function(t, e, i) {
                    var o = i(8081),
                        s = i.n(o),
                        a = i(3645),
                        n = i.n(a),
                        r = i(1667),
                        l = i.n(r),
                        c = new URL(i(6468), i.b),
                        u = n()(s()),
                        m = l()(c);
                    u.push([t.id, '#template-customizer{font-family:"Public Sans",BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol" !important;font-size:inherit !important;position:fixed;top:0;right:0;height:100%;z-index:99999999;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column;width:400px;background:#fff;-webkit-box-shadow:0 0 20px 0 rgba(0,0,0,.2);box-shadow:0 0 20px 0 rgba(0,0,0,.2);-webkit-transition:all .2s ease-in;-o-transition:all .2s ease-in;transition:all .2s ease-in;-webkit-transform:translateX(420px);-ms-transform:translateX(420px);transform:translateX(420px)}#template-customizer h5{position:relative;font-size:11px}#template-customizer>h5{flex:0 0 auto}#template-customizer .disabled{color:#d1d2d3 !important}#template-customizer .form-label{font-size:.9375rem}#template-customizer .form-check-label{font-size:.8125rem}#template-customizer .template-customizer-t-panel_header{font-size:1.125rem}#template-customizer.template-customizer-open{-webkit-transition-delay:.1s;-o-transition-delay:.1s;transition-delay:.1s;-webkit-transform:none !important;-ms-transform:none !important;transform:none !important}#template-customizer.template-customizer-open .custom-option.checked{color:var(--bs-primary);border-width:2px}#template-customizer.template-customizer-open .custom-option.checked .custom-option-content{border:none}#template-customizer.template-customizer-open .custom-option .custom-option-content{border:1px solid rgba(0,0,0,0)}#template-customizer .template-customizer-header a:hover{color:inherit !important}#template-customizer .template-customizer-open-btn{position:absolute;top:180px;left:0;z-index:-1;display:block;width:42px;height:42px;border-top-left-radius:15%;border-bottom-left-radius:15%;background:var(--bs-primary);color:#fff !important;text-align:center;font-size:18px !important;line-height:42px;opacity:1;-webkit-transition:all .1s linear .2s;-o-transition:all .1s linear .2s;transition:all .1s linear .2s;-webkit-transform:translateX(-62px);-ms-transform:translateX(-62px);transform:translateX(-62px)}@media(max-width: 991.98px){#template-customizer .template-customizer-open-btn{top:145px}}.dark-style #template-customizer .template-customizer-open-btn{background:var(--bs-primary)}#template-customizer .template-customizer-open-btn::before{content:"";width:22px;height:22px;display:block;background-size:100% 100%;position:absolute;background-image:url(' + m + ');margin:10px}.customizer-hide #template-customizer .template-customizer-open-btn{display:none}[dir=rtl] #template-customizer .template-customizer-open-btn{border-radius:0;border-top-right-radius:15%;border-bottom-right-radius:15%}[dir=rtl] #template-customizer .template-customizer-open-btn::before{margin-left:-2px}#template-customizer.template-customizer-open .template-customizer-open-btn{opacity:0;-webkit-transition-delay:0s;-o-transition-delay:0s;transition-delay:0s;-webkit-transform:none !important;-ms-transform:none !important;transform:none !important}#template-customizer .template-customizer-inner{position:relative;overflow:auto;-webkit-box-flex:0;-ms-flex:0 1 auto;flex:0 1 auto;opacity:1;-webkit-transition:opacity .2s;-o-transition:opacity .2s;transition:opacity .2s}#template-customizer .template-customizer-inner>div:first-child>hr:first-of-type{display:none !important}#template-customizer .template-customizer-inner>div:first-child>h5:first-of-type{padding-top:0 !important}#template-customizer .template-customizer-themes-inner{position:relative;opacity:1;-webkit-transition:opacity .2s;-o-transition:opacity .2s;transition:opacity .2s}#template-customizer .template-customizer-theme-item{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;align-items:center;-ms-flex-align:center;-webkit-box-flex:1;-ms-flex:1 1 100%;flex:1 1 100%;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;margin-bottom:10px;padding:0 24px;width:100%;cursor:pointer}#template-customizer .template-customizer-theme-item input{position:absolute;z-index:-1;opacity:0}#template-customizer .template-customizer-theme-item input~span{opacity:.25;-webkit-transition:all .2s;-o-transition:all .2s;transition:all .2s}#template-customizer .template-customizer-theme-item .template-customizer-theme-checkmark{display:inline-block;width:6px;height:12px;border-right:1px solid;border-bottom:1px solid;opacity:0;-webkit-transition:all .2s;-o-transition:all .2s;transition:all .2s;-webkit-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg)}[dir=rtl] #template-customizer .template-customizer-theme-item .template-customizer-theme-checkmark{border-right:none;border-left:1px solid;-webkit-transform:rotate(-45deg);-ms-transform:rotate(-45deg);transform:rotate(-45deg)}#template-customizer .template-customizer-theme-item input:checked:not([disabled])~span,#template-customizer .template-customizer-theme-item:hover input:not([disabled])~span{opacity:1}#template-customizer .template-customizer-theme-item input:checked:not([disabled])~span .template-customizer-theme-checkmark{opacity:1}#template-customizer .template-customizer-theme-colors span{display:block;margin:0 1px;width:10px;height:10px;border-radius:50%;-webkit-box-shadow:0 0 0 1px rgba(0,0,0,.1) inset;box-shadow:0 0 0 1px rgba(0,0,0,.1) inset}#template-customizer.template-customizer-loading .template-customizer-inner,#template-customizer.template-customizer-loading-theme .template-customizer-themes-inner{opacity:.2}#template-customizer.template-customizer-loading .template-customizer-inner::after,#template-customizer.template-customizer-loading-theme .template-customizer-themes-inner::after{content:"";position:absolute;top:0;right:0;bottom:0;left:0;z-index:999;display:block}@media(max-width: 1200px){#template-customizer{display:none;visibility:hidden !important}}@media(max-width: 575.98px){#template-customizer{width:300px;-webkit-transform:translateX(320px);-ms-transform:translateX(320px);transform:translateX(320px)}}.layout-menu-100vh #template-customizer{height:100vh}.template-customizer-colors-options .col-2{flex:0 0 auto;width:20%}.template-customizer-colors-options .col-2 svg{margin-bottom:0 !important;transform:rotate(-45deg)}.template-customizer-colors-options .custom-option label{display:flex;justify-content:center;padding:6px 0px !important;margin-bottom:0 !important}.template-customizer-colors-options .custom-option-body-color .i-color{background:var(--v-color);width:30px;height:30px;border-radius:5px;margin-bottom:0}[dir=rtl] #template-customizer{right:auto;left:0;-webkit-transform:translateX(-420px);-ms-transform:translateX(-420px);transform:translateX(-420px)}[dir=rtl] #template-customizer .template-customizer-open-btn{right:0;left:auto;-webkit-transform:translateX(62px);-ms-transform:translateX(62px);transform:translateX(62px)}[dir=rtl] #template-customizer .template-customizer-close-btn{right:auto;left:0}#template-customizer .template-customizer-layouts-options[disabled]{opacity:.5;pointer-events:none}[dir=rtl] .template-customizer-t-style_switch_light{padding-right:0 !important}', ""]), e.Z = u
                },
                3645: function(t) {
                    t.exports = function(t) {
                        var e = [];
                        return e.toString = function() {
                            return this.map((function(e) {
                                var i = "",
                                    o = void 0 !== e[5];
                                return e[4] && (i += "@supports (".concat(e[4], ") {")), e[2] && (i += "@media ".concat(e[2], " {")), o && (i += "@layer".concat(e[5].length > 0 ? " ".concat(e[5]) : "", " {")), i += t(e), o && (i += "}"), e[2] && (i += "}"), e[4] && (i += "}"), i
                            })).join("")
                        }, e.i = function(t, i, o, s, a) {
                            "string" == typeof t && (t = [
                                [null, t, void 0]
                            ]);
                            var n = {};
                            if (o)
                                for (var r = 0; r < this.length; r++) {
                                    var l = this[r][0];
                                    null != l && (n[l] = !0)
                                }
                            for (var c = 0; c < t.length; c++) {
                                var u = [].concat(t[c]);
                                o && n[u[0]] || (void 0 !== a && (void 0 === u[5] || (u[1] = "@layer".concat(u[5].length > 0 ? " ".concat(u[5]) : "", " {").concat(u[1], "}")), u[5] = a), i && (u[2] ? (u[1] = "@media ".concat(u[2], " {").concat(u[1], "}"), u[2] = i) : u[2] = i), s && (u[4] ? (u[1] = "@supports (".concat(u[4], ") {").concat(u[1], "}"), u[4] = s) : u[4] = "".concat(s)), e.push(u))
                            }
                        }, e
                    }
                },
                1667: function(t) {
                    t.exports = function(t, e) {
                        return e || (e = {}), t ? (t = String(t.__esModule ? t.default : t), /^['"].*['"]$/.test(t) && (t = t.slice(1, -1)), e.hash && (t += e.hash), /["'() \t\n]|(%20)/.test(t) || e.needQuotes ? '"'.concat(t.replace(/"/g, '\\"').replace(/\n/g, "\\n"), '"') : t) : t
                    }
                },
                8081: function(t) {
                    t.exports = function(t) {
                        return t[1]
                    }
                },
                3379: function(t) {
                    var e = [];

                    function i(t) {
                        for (var i = -1, o = 0; o < e.length; o++)
                            if (e[o].identifier === t) {
                                i = o;
                                break
                            } return i
                    }

                    function o(t, o) {
                        for (var a = {}, n = [], r = 0; r < t.length; r++) {
                            var l = t[r],
                                c = o.base ? l[0] + o.base : l[0],
                                u = a[c] || 0,
                                m = "".concat(c, " ").concat(u);
                            a[c] = u + 1;
                            var d = i(m),
                                h = {
                                    css: l[1],
                                    media: l[2],
                                    sourceMap: l[3],
                                    supports: l[4],
                                    layer: l[5]
                                };
                            if (-1 !== d) e[d].references++, e[d].updater(h);
                            else {
                                var p = s(h, o);
                                o.byIndex = r, e.splice(r, 0, {
                                    identifier: m,
                                    updater: p,
                                    references: 1
                                })
                            }
                            n.push(m)
                        }
                        return n
                    }

                    function s(t, e) {
                        var i = e.domAPI(e);
                        return i.update(t),
                            function(e) {
                                if (e) {
                                    if (e.css === t.css && e.media === t.media && e.sourceMap === t.sourceMap && e.supports === t.supports && e.layer === t.layer) return;
                                    i.update(t = e)
                                } else i.remove()
                            }
                    }
                    t.exports = function(t, s) {
                        var a = o(t = t || [], s = s || {});
                        return function(t) {
                            t = t || [];
                            for (var n = 0; n < a.length; n++) {
                                var r = i(a[n]);
                                e[r].references--
                            }
                            for (var l = o(t, s), c = 0; c < a.length; c++) {
                                var u = i(a[c]);
                                0 === e[u].references && (e[u].updater(), e.splice(u, 1))
                            }
                            a = l
                        }
                    }
                },
                569: function(t) {
                    var e = {};
                    t.exports = function(t, i) {
                        var o = function(t) {
                            if (void 0 === e[t]) {
                                var i = document.querySelector(t);
                                if (window.HTMLIFrameElement && i instanceof window.HTMLIFrameElement) try {
                                    i = i.contentDocument.head
                                } catch (t) {
                                    i = null
                                }
                                e[t] = i
                            }
                            return e[t]
                        }(t);
                        if (!o) throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
                        o.appendChild(i)
                    }
                },
                9216: function(t) {
                    t.exports = function(t) {
                        var e = document.createElement("style");
                        return t.setAttributes(e, t.attributes), t.insert(e, t.options), e
                    }
                },
                3565: function(t, e, i) {
                    t.exports = function(t) {
                        var e = i.nc;
                        e && t.setAttribute("nonce", e)
                    }
                },
                7795: function(t) {
                    t.exports = function(t) {
                        if ("undefined" == typeof document) return {
                            update: function() {},
                            remove: function() {}
                        };
                        var e = t.insertStyleElement(t);
                        return {
                            update: function(i) {
                                ! function(t, e, i) {
                                    var o = "";
                                    i.supports && (o += "@supports (".concat(i.supports, ") {")), i.media && (o += "@media ".concat(i.media, " {"));
                                    var s = void 0 !== i.layer;
                                    s && (o += "@layer".concat(i.layer.length > 0 ? " ".concat(i.layer) : "", " {")), o += i.css, s && (o += "}"), i.media && (o += "}"), i.supports && (o += "}");
                                    var a = i.sourceMap;
                                    a && "undefined" != typeof btoa && (o += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(a)))), " */")), e.styleTagTransform(o, t, e.options)
                                }(e, t, i)
                            },
                            remove: function() {
                                ! function(t) {
                                    if (null === t.parentNode) return !1;
                                    t.parentNode.removeChild(t)
                                }(e)
                            }
                        }
                    }
                },
                4589: function(t) {
                    t.exports = function(t, e) {
                        if (e.styleSheet) e.styleSheet.cssText = t;
                        else {
                            for (; e.firstChild;) e.removeChild(e.firstChild);
                            e.appendChild(document.createTextNode(t))
                        }
                    }
                },
                6468: function(t) {
                    t.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAAAXNSR0IArs4c6QAABClJREFUaEPtmY1RFEEQhbsjUCIQIhAiUCNQIxAiECIQIxAiECIAIpAMhAiECIQI2vquZqnZvp6fhb3SK5mqq6Ju92b69bzXf6is+dI1t1+eAfztG5z1BsxsU0S+ici2iPB3vm5E5EpEDlSVv2dZswFIxv8UkZcNy+5EZGcuEHMCOBeR951uvVDVD53vVl+bE8DvDu8Pxtyo6ta/BsByg1R15Bwzqz5/LJgn34CZwfnPInI4BUB6/1hV0cSjVxcAM4PbcBZjL0XklIPN7Is3fLCkdQPpPYw/VNXj5IhPIvJWRIhSl6p60ULWBGBm30Vk123EwRxCuIzWkkjNrCZywith10ewE1Xdq4GoAjCz/RTXW44Ynt+LyBEfT43kYfbj86J3w5Q32DNcRQDpwF+dkQXDMey8xem0L3TEqB4g3PZWad8agBMRgZPeu96D1/C2Zbh3X0p80Op1xxloztN48bMQQNoc7+eLEuAoPSPiIDY4Ooo+E6ixeNXM+D3GERz2U3CIqMstLJUgJQDe+7eq6mub0NYEkLAKwEHkiBQDCZtddZCZ8d6r7JDwFkoARklHRPZUFVDVZWbwGuNrC4EfdOzFrRABh3Wnqhv+d70AEBLGFROPmeHlnM81G69UdSd6IUuM0GgUVn1uqWmg5EmMfBeEyB7Pe3txBkY+rGT8j0J+WXq/BgDkUCaqLgEAnwcRog0veMIqFAAwCy2wnw+bI2GaGboBgF9k5N0o0rUSGUb4eO0BeO9j/GYhkSHMHMTIqwGARX6p6a+nlPBl8kZuXMD9j6pKfF9aZuaFOdJCEL5D4eYb9wCYVCanrBmGyii/tIq+SLj/HQBCaM5bLzwfPqdQ6FpVHyra4IbuVbXaY7dETC2ESPNNWiIOi69CcdgSMXsh4tNSUiklMgwmC0aNd08Y5WAES6HHehM4gu97wyhBgWpgqXsrASglprDy7CwhehMZOSbK6JMSma+Fio1KltCmlBIj7gfZOGx8ppQSXrhzFnOhJ/31BDkjFHRvOd09x0mRBA9SFgxUgHpQg0q0t5ymPMlL+EnldFTfDA0NAmf+OTQ0X0sRouf7NNkYGhrOYNrxtIaGg83MNzVDSe3LXLhP7O/yrCsCz1zlWTpjWkuZAOBpX3yVnLqI1yLCOKU6qMrmP7SSrUEw54XF4WBIK5FxCMOr3lVsfGqNSmPzBXUnJTIX1jyVBq9wO6UObOpgC5GjO98vFKnTdQMZXxEsWZlDiCZMIxAbNxQOqlpVZtobejBaZNoBnRDzMFpkxvTQOD36BlrcySZuI6p1ACB6LU3wWuf5581+oHfD1vi89bz3nFUC8Nm7ZlP3nKkFbM4bWPt/MSFwklprYItwt6cmvpWJ2IVcQBCz6bLysSCv3SaANCiTsnaNRrNRqMXVVT1/BrAqz/buu/Y38Ad3KC5PARej0QAAAABJRU5ErkJggg=="
                }
            },
            e = {};

        function i(o) {
            var s = e[o];
            if (void 0 !== s) return s.exports;
            var a = e[o] = {
                id: o,
                exports: {}
            };
            return t[o](a, a.exports, i), a.exports
        }
        i.m = t, i.n = function(t) {
            var e = t && t.__esModule ? function() {
                return t.default
            } : function() {
                return t
            };
            return i.d(e, {
                a: e
            }), e
        }, i.d = function(t, e) {
            for (var o in e) i.o(e, o) && !i.o(t, o) && Object.defineProperty(t, o, {
                enumerable: !0,
                get: e[o]
            })
        }, i.o = function(t, e) {
            return Object.prototype.hasOwnProperty.call(t, e)
        }, i.r = function(t) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
                value: "Module"
            }), Object.defineProperty(t, "__esModule", {
                value: !0
            })
        }, i.b = document.baseURI || self.location.href, i.nc = void 0;
        var o = {};
        return function() {
            i.r(o), i.d(o, {
                TemplateCustomizer: function() {
                    return F
                }
            });
            var t = i(3379),
                e = i.n(t),
                s = i(7795),
                a = i.n(s),
                n = i(569),
                r = i.n(n),
                l = i(3565),
                c = i.n(l),
                u = i(9216),
                m = i.n(u),
                d = i(4589),
                h = i.n(d),
                p = i(7621),
                y = {};

            function g(t) {
                return g = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(t) {
                    return typeof t
                } : function(t) {
                    return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
                }, g(t)
            }

            function v(t, e) {
                for (var i = 0; i < e.length; i++) {
                    var o = e[i];
                    o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(t, f(o.key), o)
                }
            }

            function f(t) {
                var e = function(t, e) {
                    if ("object" != g(t) || !t) return t;
                    var i = t[Symbol.toPrimitive];
                    if (void 0 !== i) {
                        var o = i.call(t, "string");
                        if ("object" != g(o)) return o;
                        throw new TypeError("@@toPrimitive must return a primitive value.")
                    }
                    return String(t)
                }(t);
                return "symbol" == g(e) ? e : e + ""
            }
            y.styleTagTransform = h(), y.setAttributes = c(), y.insert = r().bind(null, "head"), y.domAPI = a(), y.insertStyleElement = m(), e()(p.Z, y), p.Z && p.Z.locals && p.Z.locals;
            var b, _ = ["rtl", "style", "headerType", "contentLayout", "layoutCollapsed", "showDropdownOnHover", "layoutNavbarOptions", "layoutFooterFixed", "themes", "colors", "fonts"],
                w = {
                    "color-1": '<svg class="customizer-btn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26"><circle cx="12" cy="12" r="10" fill="#00C3F9"></circle><path d="M2,12 a1,1 1 1,0 20,0" fill="#573BFF"></path></svg>',
                    "color-2": '<svg class="customizer-btn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26"><circle cx="12" cy="12" r="10" fill="#FF00FF"></circle><path d="M2,12 a1,1 1 1,0 20,0" fill="#FD8D00"></path></svg>',
                    "color-3": '<svg class="customizer-btn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26"><circle cx="12" cy="12" r="10" fill="#c02026"></circle><path d="M2,12 a1,1 1 1,0 20,0" fill="#16db65"></path></svg>',
                    "color-4": '<svg class="customizer-btn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26"><circle cx="12" cy="12" r="10" fill="#EA6A12"></circle><path d="M2,12 a1,1 1 1,0 20,0" fill="#6410F1"></path></svg>',
                    "color-5": '<svg class="customizer-btn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26"><circle cx="12" cy="12" r="10" fill="#E586B3"></circle><path d="M2,12 a1,1 1 1,0 20,0" fill="#25C799"></path></svg>'
                },
                x = ["light", "dark", "system"],
                S = ["sticky", "static", "hidden"],
                z = document.documentElement.classList;
            b = z.contains("layout-navbar-fixed") ? "sticky" : z.contains("layout-navbar-hidden") ? "hidden" : "static";
            var C = document.getElementsByTagName("HTML")[0].getAttribute("data-theme") || 0,
                k = z.contains("dark-style") ? "dark" : "light",
                T = "rtl" === document.documentElement.getAttribute("dir"),
                L = !!z.contains("layout-menu-collapsed"),
                N = b,
                A = z.contains("layout-wide") ? "wide" : "compact",
                E = !!z.contains("layout-footer-fixed"),
                O = z.contains("layout-menu-offcanvas") ? "static-offcanvas" : z.contains("layout-menu-fixed") ? "fixed" : z.contains("layout-menu-fixed-offcanvas") ? "fixed-offcanvas" : "static",
                F = function() {
                    function t(e) {
                        var i = e.cssPath,
                            o = e.themesPath,
                            s = e.cssFilenamePattern,
                            a = e.displayCustomizer,
                            n = e.controls,
                            r = e.defaultTextDir,
                            l = e.defaultHeaderType,
                            c = e.defaultContentLayout,
                            u = e.defaultMenuCollapsed,
                            m = e.defaultShowDropdownOnHover,
                            d = e.defaultNavbarType,
                            h = e.defaultFooterFixed,
                            p = e.styles,
                            y = e.navbarOptions,
                            g = e.defaultStyle,
                            v = e.defaultColor,
                            f = e.availableContentLayouts,
                            b = e.availableDirections,
                            w = e.availableStyles,
                            z = e.availableColors,
                            F = e.availableThemes,
                            I = e.availableLayouts,
                            D = e.availableHeaderTypes,
                            q = e.availableNavbarOptions,
                            P = e.defaultTheme,
                            M = e.pathResolver,
                            B = e.onSettingsChange,
                            H = e.lang,
                            R = e.colorPrimary;
                        if (function(t, e) {
                                if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
                            }(this, t), !this._ssr) {
                            if (!window.Helpers) throw new Error("window.Helpers required.");
                            if (this.settings = {}, this.settings.cssPath = i, this.settings.themesPath = o, this.settings.cssFilenamePattern = s || "%name%.css", this.settings.displayCustomizer = void 0 === a || a, this.settings.controls = n || _, this.settings.defaultTextDir = "rtl" === r || T, this.settings.defaultHeaderType = l || O, this.settings.defaultMenuCollapsed = void 0 !== u ? u : L, this.settings.defaultContentLayout = void 0 !== c ? c : A, this.settings.defaultShowDropdownOnHover = void 0 === m || m, this.settings.defaultNavbarType = void 0 !== d ? d : N, this.settings.defaultFooterFixed = void 0 !== h ? h : E, this.settings.availableDirections = b || t.DIRECTIONS, this.settings.availableStyles = w || t.STYLES, this.settings.availableColors = z || t.COLORS, this.settings.availableThemes = F || t.THEMES, this.settings.availableHeaderTypes = D || t.HEADER_TYPES, this.settings.availableContentLayouts = f || t.CONTENT, this.settings.availableLayouts = I || t.LAYOUTS, this.settings.availableNavbarOptions = q || t.NAVBAR_OPTIONS, this.settings.defaultTheme = this._getDefaultTheme(void 0 !== P ? P : C), this.settings.colorPrimary = R, this.settings.styles = p || x, this.settings.navbarOptions = y || S, this.settings.defaultStyle = g || k, this.settings.defaultColor = v || "", this.settings.lang = H || "en", this.pathResolver = M || function(t) {
                                    return t
                                }, this.settings.styles.length < 2) {
                                var U = this.settings.controls.indexOf("style"); - 1 !== U && (this.settings.controls = this.settings.controls.slice(0, U).concat(this.settings.controls.slice(U + 1)))
                            }
                            this.settings.onSettingsChange = "function" == typeof B ? B : function() {}, this._loadSettings(), this._listeners = [], this._controls = {}, this._initDirection(), this._initStyle(), this._initTheme(), this.setLayoutType(this.settings.headerType, !1), this.setContentLayout(this.settings.contentLayout, !1), this.setDropdownOnHover(this.settings.showDropdownOnHover, !1), this.setLayoutNavbarOption(this.settings.layoutNavbarOptions, !1), this.setLayoutFooterFixed(this.settings.layoutFooterFixed, !1), this.setColor(this.settings.color, !1), this._setup()
                        }
                    }
                    return e = t, i = [{
                        key: "setRtl",
                        value: function(t) {
                            this._hasControls("rtl") && (this._setSetting("Rtl", String(t)), window.location.reload())
                        }
                    }, {
                        key: "setContentLayout",
                        value: function(t) {
                            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
                            this._hasControls("contentLayout") && (this.settings.contentLayout = t, e && this._setSetting("contentLayout", t), window.Helpers.setContentLayout(t), e && this.settings.onSettingsChange.call(this, this.settings))
                        }
                    }, {
                        key: "setStyle",
                        value: function(t) {
                            this._setSetting("Style", t), window.location.reload()
                        }
                    }, {
                        key: "setColor",
                        value: function(t) {
                            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
                            this._hasControls("colors") && (this.settings.color = t, document.documentElement.setAttribute("data-bs-theme-color", t), e && this._setSetting("Color", t), this._setSetting("ColorPrimaryUse", this.settings.colorPrimary[t].primary), e && this.settings.onSettingsChange.call(this, this.settings))
                        }
                    }, {
                        key: "setTheme",
                        value: function(t) {
                            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1],
                                i = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : null;
                            if (this._hasControls("themes")) {
                                var o = this._getThemeByName(t);
                                if (o) {
                                    this.settings.theme = o, e && this._setSetting("Theme", t);
                                    var s, a, n, r = this.pathResolver(this.settings.themesPath + this.settings.cssFilenamePattern.replace("%name%", t + ("light" !== this.settings.style ? "-".concat(this.settings.style) : "")));
                                    this._loadStylesheets((s = {}, a = r, n = document.querySelector(".template-customizer-theme-css"), (a = f(a)) in s ? Object.defineProperty(s, a, {
                                        value: n,
                                        enumerable: !0,
                                        configurable: !0,
                                        writable: !0
                                    }) : s[a] = n, s), i || function() {}), e && this.settings.onSettingsChange.call(this, this.settings)
                                }
                            }
                        }
                    }, {
                        key: "setLayoutType",
                        value: function(t) {
                            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
                            if (this._hasControls("headerType") && ("static" === t || "static-offcanvas" === t || "fixed" === t || "fixed-offcanvas" === t)) {
                                this.settings.headerType = t, e && this._setSetting("LayoutType", t), window.Helpers.setPosition("fixed" === t || "fixed-offcanvas" === t, "static-offcanvas" === t || "fixed-offcanvas" === t), e && this.settings.onSettingsChange.call(this, this.settings);
                                var i = window.Helpers.menuPsScroll,
                                    o = window.PerfectScrollbar;
                                "fixed" === this.settings.headerType || "fixed-offcanvas" === this.settings.headerType ? o && i && (window.Helpers.menuPsScroll.destroy(), i = new o(document.querySelector(".menu-inner"), {
                                    suppressScrollX: !0,
                                    wheelPropagation: !1
                                }), window.Helpers.menuPsScroll = i) : i && window.Helpers.menuPsScroll.destroy()
                            }
                        }
                    }, {
                        key: "setDropdownOnHover",
                        value: function(t) {
                            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
                            if (this._hasControls("showDropdownOnHover")) {
                                if (this.settings.showDropdownOnHover = t, e && this._setSetting("ShowDropdownOnHover", t), window.Helpers.mainMenu) {
                                    window.Helpers.mainMenu.destroy(), config.showDropdownOnHover = t;
                                    var i = window.Menu;
                                    window.Helpers.mainMenu = new i(document.getElementById("layout-menu"), {
                                        orientation: "horizontal",
                                        closeChildren: !0,
                                        showDropdownOnHover: config.showDropdownOnHover
                                    })
                                }
                                e && this.settings.onSettingsChange.call(this, this.settings)
                            }
                        }
                    }, {
                        key: "setLayoutNavbarOption",
                        value: function(t) {
                            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
                            this._hasControls("layoutNavbarOptions") && (this.settings.layoutNavbarOptions = t, e && this._setSetting("FixedNavbarOption", t), window.Helpers.setNavbar(t), e && this.settings.onSettingsChange.call(this, this.settings))
                        }
                    }, {
                        key: "setLayoutFooterFixed",
                        value: function(t) {
                            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
                            this.settings.layoutFooterFixed = t, e && this._setSetting("FixedFooter", t), window.Helpers.setFooterFixed(t), e && this.settings.onSettingsChange.call(this, this.settings)
                        }
                    }, {
                        key: "setLang",
                        value: function(e) {
                            var i = this,
                                o = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1],
                                s = arguments.length > 2 && void 0 !== arguments[2] && arguments[2];
                            if (e !== this.settings.lang || s) {
                                if (!t.LANGUAGES[e]) throw new Error('Language "'.concat(e, '" not found!'));
                                var a = t.LANGUAGES[e];
                                ["panel_header", "panel_sub_header", "theming_header", "style_label", "color_label", "font_label", "style_switch_light", "style_switch_dark", "layout_header", "layout_label", "layout_header_label", "content_label", "layout_static", "layout_offcanvas", "layout_fixed", "layout_fixed_offcanvas", "layout_dd_open_label", "layout_navbar_label", "layout_footer_label", "misc_header", "theme_label", "direction_label"].forEach((function(t) {
                                    var e = i.container.querySelector(".template-customizer-t-".concat(t));
                                    e && (e.textContent = a[t])
                                }));
                                for (var n = a.themes || {}, r = this.container.querySelectorAll(".template-customizer-theme-item") || [], l = 0, c = r.length; l < c; l++) {
                                    var u = r[l].querySelector('input[type="radio"]').value;
                                    r[l].querySelector(".template-customizer-theme-name").textContent = n[u] || this._getThemeByName(u).title
                                }
                                this.settings.lang = e, o && this._setSetting("Lang", e), o && this.settings.onSettingsChange.call(this, this.settings)
                            }
                        }
                    }, {
                        key: "update",
                        value: function() {
                            if (!this._ssr) {
                                var t = !!document.querySelector(".layout-navbar"),
                                    e = !!document.querySelector(".layout-menu"),
                                    i = !!document.querySelector(".layout-menu-horizontal.menu, .layout-menu-horizontal .menu"),
                                    o = (document.querySelector(".layout-wrapper.layout-navbar-full"), !!document.querySelector(".content-footer"));
                                this._controls.showDropdownOnHover && (e ? (this._controls.showDropdownOnHover.setAttribute("disabled", "disabled"), this._controls.showDropdownOnHover.classList.add("disabled")) : (this._controls.showDropdownOnHover.removeAttribute("disabled"), this._controls.showDropdownOnHover.classList.remove("disabled"))), this._controls.layoutNavbarOptions && (t ? (this._controls.layoutNavbarOptions.removeAttribute("disabled"), this._controls.layoutNavbarOptionsW.classList.remove("disabled")) : (this._controls.layoutNavbarOptions.setAttribute("disabled", "disabled"), this._controls.layoutNavbarOptionsW.classList.add("disabled")), i && t && "fixed" === this.settings.headerType && (this._controls.layoutNavbarOptions.setAttribute("disabled", "disabled"), this._controls.layoutNavbarOptionsW.classList.add("disabled"))), this._controls.layoutFooterFixed && (o ? (this._controls.layoutFooterFixed.removeAttribute("disabled"), this._controls.layoutFooterFixedW.classList.remove("disabled")) : (this._controls.layoutFooterFixed.setAttribute("disabled", "disabled"), this._controls.layoutFooterFixedW.classList.add("disabled"))), this._controls.headerType && (e || i ? this._controls.headerType.removeAttribute("disabled") : this._controls.headerType.setAttribute("disabled", "disabled"))
                            }
                        }
                    }, {
                        key: "clearLocalStorage",
                        value: function() {
                            if (!this._ssr) {
                                var t = this._getLayoutName();
                                ["Theme", "Style", "LayoutCollapsed", "FixedNavbarOption", "LayoutType", "contentLayout", "Rtl", "Lang"].forEach((function(e) {
                                    var i = "templateCustomizer-".concat(t, "--").concat(e);
                                    localStorage.removeItem(i)
                                })), this._showResetBtnNotification(!1)
                            }
                        }
                    }, {
                        key: "destroy",
                        value: function() {
                            this._ssr || (this._cleanup(), this.settings = null, this.container.parentNode.removeChild(this.container), this.container = null)
                        }
                    }, {
                        key: "_loadSettings",
                        value: function() {
                            var t, e, i = this._getSetting("Rtl"),
                                o = this._getSetting("Style"),
                                s = this._getSetting("Theme"),
                                a = this._getSetting("Color"),
                                n = this._getSetting("contentLayout"),
                                r = this._getSetting("LayoutCollapsed"),
                                l = this._getSetting("ShowDropdownOnHover"),
                                c = this._getSetting("FixedNavbarOption"),
                                u = this._getSetting("FixedFooter"),
                                m = this._getSetting("LayoutType");
                            "" !== i || "" !== o || "" !== s || "" !== n || "" !== r || "" !== c || "" !== m ? this._showResetBtnNotification(!0) : this._showResetBtnNotification(!1), t = "" !== m && -1 !== ["static", "static-offcanvas", "fixed", "fixed-offcanvas"].indexOf(m) ? m : this.settings.defaultHeaderType, this.settings.headerType = t, this.settings.color = "" != a ? a : this.settings.defaultColor, this.settings.rtl = "" !== i ? "true" === i : this.settings.defaultTextDir, this.settings.stylesOpt = -1 !== this.settings.styles.indexOf(o) ? o : this.settings.defaultStyle, document.documentElement.setAttribute("data-bs-theme-color", this.settings.color), "system" === this.settings.stylesOpt ? window.matchMedia("(prefers-color-scheme: dark)").matches ? (this.settings.style = "dark", document.cookie = "style=dark") : (this.settings.style = "light", document.cookie = "style=light") : (document.cookie = "style=; expires=Thu, 01 Jan 2000 00:00:00 UTC; path=/;", this.settings.style = -1 !== this.settings.styles.indexOf(o) ? o : this.settings.defaultStyle), -1 === this.settings.styles.indexOf(this.settings.style) && (this.settings.style = this.settings.styles[0]), this.settings.contentLayout = "" !== n ? n : this.settings.defaultContentLayout, this.settings.layoutCollapsed = "" !== r ? "true" === r : this.settings.defaultMenuCollapsed, this.settings.showDropdownOnHover = "" !== l ? "true" === l : this.settings.defaultShowDropdownOnHover, e = "" !== c && -1 !== ["static", "sticky", "hidden"].indexOf(c) ? c : this.settings.defaultNavbarType, this.settings.layoutNavbarOptions = e, this.settings.layoutFooterFixed = "" !== u ? "true" === u : this.settings.defaultFooterFixed, this.settings.theme = this._getThemeByName(this._getSetting("Theme"), !0), this._hasControls("rtl") || (this.settings.rtl = "rtl" === document.documentElement.getAttribute("dir")), this._hasControls("style") || (this.settings.style = z.contains("dark-style") ? "dark" : "light"), this._hasControls("contentLayout") || (this.settings.contentLayout = null), this._hasControls("headerType") || (this.settings.headerType = null), this._hasControls("layoutCollapsed") || (this.settings.layoutCollapsed = null), this._hasControls("layoutNavbarOptions") || (this.settings.layoutNavbarOptions = null), this._hasControls("themes") || (this.settings.theme = null)
                        }
                    }, {
                        key: "_setup",
                        value: function() {
                            var t = this,
                                e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : document,
                                i = function(e, i, o, s, a) {
                                    return a = a || e, t._getElementFromString('<div class="col-4 px-2">\n      <div class="form-check custom-option custom-option-icon mb-0">\n        <label class="form-check-label custom-option-content p-0" for="'.concat(o).concat(e, '">\n          <span class="custom-option-body mb-0">\n            <img src="').concat(assetsPath, "img/customizer/").concat(a).concat(s ? "-dark" : "", '.svg" alt="').concat(i, '" class="img-fluid scaleX-n1-rtl" />\n          </span>\n          <input\n            name="').concat(o, '"\n            class="form-check-input d-none"\n            type="radio"\n            value="').concat(e, '"\n            id="').concat(o).concat(e, '" />\n        </label>\n      </div>\n      <label class="form-check-label small text-nowrap" for="').concat(o).concat(e, '">').concat(i, "</label>\n    </div>"))
                                };
                            this._cleanup(), this.container = this._getElementFromString('<div id="template-customizer" class="invert-bg-white"> <a href="javascript:void(0)" class="template-customizer-open-btn" tabindex="-1"></a> <div class="p-4 m-0 lh-1 border-bottom template-customizer-header position-relative py-3"> <h4 class="template-customizer-t-panel_header mb-2"></h4> <p class="template-customizer-t-panel_sub_header mb-0"></p> <div class="d-flex align-items-center gap-2 position-absolute end-0 top-0 mt-4 me-3"> <a href="javascript:void(0)" class="template-customizer-reset-btn text-body" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Reset Customizer"><i class="ti ti-refresh ti-sm"></i><span class="badge rounded-pill bg-danger badge-dot badge-notifications d-none"></span></a> <a href="javascript:void(0)" class="template-customizer-close-btn fw-light text-body" tabindex="-1"><i class="ti ti-x ti-sm"></i></a> </div> </div> <div class="template-customizer-inner pt-4"> <div class="template-customizer-theming"> <h5 class="m-0 px-4 py-4 lh-1 d-block"> <span class="template-customizer-t-theming_header bg-label-primary rounded-1 py-1 px-2 fs-big"></span> </h5> <div class="m-0 px-4 pb-3 pt-1 template-customizer-colors w-100"> <label for="customizerColor" class="form-label d-block template-customizer-t-color_label"></label> <div class="row px-1 template-customizer-colors-options"></div> </div> <div class="m-0 px-4 pb-3 pt-1 template-customizer-style w-100"> <label for="customizerStyle" class="form-label d-block template-customizer-t-style_label"></label> <div class="row px-1 template-customizer-styles-options"></div> </div> <div class="m-0 px-4 pt-3 template-customizer-themes w-100"> <label for="customizerTheme" class="form-label template-customizer-t-theme_label"></label> <div class="row px-1 template-customizer-themes-options"></div> </div> </div> <div class="template-customizer-layout"> <hr class="m-0 px-4 my-4"/> <h5 class="m-0 px-4 pb-4 pt-2 d-block"> <span class="template-customizer-t-layout_header bg-label-primary rounded-1 py-1 px-2 fs-big"></span> </h5> <div class="m-0 px-4 pb-3 d-block template-customizer-layouts"> <label for="customizerStyle" class="form-label d-block template-customizer-t-layout_label"></label> <div class="row px-1 template-customizer-layouts-options"></div> </div> <div class="m-0 px-4 pb-3 template-customizer-headerOptions w-100"> <label for="customizerHeader" class="form-label template-customizer-t-layout_header_label"></label> <div class="row px-1 template-customizer-header-options"></div> </div> <div class="m-0 px-4 pb-3 template-customizer-layoutNavbarOptions w-100"> <label for="customizerNavbar" class="form-label template-customizer-t-layout_navbar_label"></label> <div class="row px-1 template-customizer-navbar-options"></div> </div> <div class="m-0 px-4 pb-3 template-customizer-content w-100"> <label for="customizerContent" class="form-label template-customizer-t-content_label"></label> <div class="row px-1 template-customizer-content-options"></div> </div> <div class="m-0 px-4 pb-3 template-customizer-directions w-100"> <label for="customizerDirection" class="form-label template-customizer-t-direction_label"></label> <div class="row px-1 template-customizer-directions-options"></div> </div> </div> </div> </div> ');
                            var o = this.container;
                            this.settings.displayCustomizer ? o.setAttribute("style", "visibility: visible") : o.setAttribute("style", "visibility: hidden");
                            var s = this.container.querySelector(".template-customizer-open-btn"),
                                a = function() {
                                    t.container.classList.add("template-customizer-open"), t.update(), t._updateInterval && clearInterval(t._updateInterval), t._updateInterval = setInterval((function() {
                                        t.update()
                                    }), 500)
                                };
                            s.addEventListener("click", a), this._listeners.push([s, "click", a]);
                            var n = this.container.querySelector(".template-customizer-reset-btn"),
                                r = function() {
                                    t.clearLocalStorage(), window.location.reload()
                                };
                            n.addEventListener("click", r), this._listeners.push([n, "click", r]);
                            var l = this.container.querySelector(".template-customizer-close-btn"),
                                c = function() {
                                    t.container.classList.remove("template-customizer-open"), t._updateInterval && (clearInterval(t._updateInterval), t._updateInterval = null)
                                };
                            l.addEventListener("click", c), this._listeners.push([l, "click", c]);
                            var u = this.container.querySelector(".template-customizer-style"),
                                m = u.querySelector(".template-customizer-styles-options");
                            if (this._hasControls("style")) {
                                this.settings.availableStyles.forEach((function(t) {
                                    var e = i(t.name, t.title, "customRadioIcon", z.contains("dark-style"));
                                    m.appendChild(e)
                                })), m.querySelector('input[value="'.concat(this.settings.stylesOpt, '"]')).setAttribute("checked", "checked");
                                var d = function(e) {
                                    t._loadingState(!0), t.setStyle(e.target.value, !0, (function() {
                                        t._loadingState(!1)
                                    }))
                                };
                                m.addEventListener("change", d), this._listeners.push([m, "change", d])
                            } else u.parentNode.removeChild(u);
                            var h = this.container.querySelector(".template-customizer-colors").querySelector(".template-customizer-colors-options");
                            if (this._hasControls("colors")) {
                                this.settings.availableColors.forEach((function(e) {
                                    var i, o, s, a = (i = e.name, o = e.title, s = "colorRadios", z.contains("dark-style"), t._getElementFromString('<div class="col-2 px-2">\n      <div class="form-check custom-option custom-option-icon mb-0">\n        <label class="form-check-label custom-option-content p-0" for="'.concat(s).concat(i, '">\n          <span class="custom-option-body custom-option-body-color mb-0">\n              ').concat(w[i], '\n          </span>\n          <input\n            name="').concat(s, '"\n            class="form-check-input d-none"\n            type="radio"\n            value="').concat(i, '"\n            id="').concat(s).concat(i, '" />\n        </label>\n      </div>\n      <label class="form-check-label small text-nowrap" for="').concat(s).concat(i, '">').concat(o, "</label>\n    </div>")));
                                    h.appendChild(a)
                                })), "" != this.settings.color && h.querySelector('input[value="'.concat(this.settings.color, '"]')) && h.querySelector('input[value="'.concat(this.settings.color, '"]')).setAttribute("checked", "checked");
                                var p = function(e) {
                                    t._loading = !0, t._loadingState(!0, !0), t.setColor(e.target.value, !0, (function() {
                                        t._loading = !1, t._loadingState(!1, !0)
                                    }))
                                };
                                h.addEventListener("change", p), this._listeners.push([h, "change", p])
                            }
                            var y = this.container.querySelector(".template-customizer-themes"),
                                g = y.querySelector(".template-customizer-themes-options");
                            if (this._hasControls("themes")) {
                                this.settings.availableThemes.forEach((function(t) {
                                    var e;
                                    e = "theme-semi-dark" === t.name ? "semi-dark" : "theme-bordered" === t.name ? "border" : "default";
                                    var o = i(t.name, t.title, "themeRadios", z.contains("dark-style"), e);
                                    g.appendChild(o)
                                })), g.querySelector('input[value="'.concat(this.settings.theme.name, '"]')).setAttribute("checked", "checked");
                                var v = function(e) {
                                    t._loading = !0, t._loadingState(!0, !0), t.setTheme(e.target.value, !0, (function() {
                                        t._loading = !1, t._loadingState(!1, !0)
                                    }))
                                };
                                g.addEventListener("change", v), this._listeners.push([g, "change", v])
                            } else y.parentNode.removeChild(y);
                            var f = this.container.querySelector(".template-customizer-theming");
                            this._hasControls("style") || this._hasControls("themes") || f.parentNode.removeChild(f);
                            var b = this.container.querySelector(".template-customizer-layout");
                            if (this._hasControls("rtl headerType contentLayout layoutCollapsed layoutNavbarOptions", !0)) {
                                var _ = this.container.querySelector(".template-customizer-directions");
                                if (this._hasControls("rtl") && rtlSupport) {
                                    var x = _.querySelector(".template-customizer-directions-options");
                                    this.settings.availableDirections.forEach((function(t) {
                                        var e = i(t.name, t.title, "directionRadioIcon", z.contains("dark-style"));
                                        x.appendChild(e)
                                    })), x.querySelector('input[value="'.concat(this.settings.rtl ? "rtl" : "ltr", '"]')).setAttribute("checked", "checked");
                                    var S = function(e) {
                                        t._loadingState(!0), "ar" === t._getSetting("Lang") ? t._setSetting("Lang", "en") : t._setSetting("Lang", "ar"), t.setRtl("rtl" === e.target.value, !0, (function() {
                                            t._loadingState(!1)
                                        }))
                                    };
                                    x.addEventListener("change", S), this._listeners.push([x, "change", S])
                                } else _.parentNode.removeChild(_);
                                var C = this.container.querySelector(".template-customizer-headerOptions"),
                                    k = document.documentElement.getAttribute("data-template").split("-");
                                if (this._hasControls("headerType")) {
                                    var T = C.querySelector(".template-customizer-header-options");
                                    setTimeout((function() {
                                        k.includes("vertical") && C.parentNode.removeChild(C)
                                    }), 100), this.settings.availableHeaderTypes.forEach((function(t) {
                                        var e = i(t.name, t.title, "headerRadioIcon", z.contains("dark-style"), "horizontal-".concat(t.name));
                                        T.appendChild(e)
                                    })), T.querySelector('input[value="'.concat(this.settings.headerType, '"]')).setAttribute("checked", "checked");
                                    var L = function(e) {
                                        t.setLayoutType(e.target.value)
                                    };
                                    T.addEventListener("change", L), this._listeners.push([T, "change", L])
                                } else C.parentNode.removeChild(C);
                                var N = this.container.querySelector(".template-customizer-content");
                                if (this._hasControls("contentLayout")) {
                                    var A = N.querySelector(".template-customizer-content-options");
                                    this.settings.availableContentLayouts.forEach((function(t) {
                                        var e = i(t.name, t.title, "contentRadioIcon", z.contains("dark-style"));
                                        A.appendChild(e)
                                    })), A.querySelector('input[value="'.concat(this.settings.contentLayout, '"]')).setAttribute("checked", "checked");
                                    var E = function(e) {
                                        t._loading = !0, t._loadingState(!0, !0), t.setContentLayout(e.target.value, !0, (function() {
                                            t._loading = !1, t._loadingState(!1, !0)
                                        }))
                                    };
                                    A.addEventListener("change", E), this._listeners.push([A, "change", E])
                                } else N.parentNode.removeChild(N);
                                var O = this.container.querySelector(".template-customizer-layouts");
                                if (this._hasControls("layoutCollapsed")) {
                                    setTimeout((function() {
                                        document.querySelector(".layout-menu-horizontal") && O.parentNode.removeChild(O)
                                    }), 100);
                                    var F = O.querySelector(".template-customizer-layouts-options");
                                    this.settings.availableLayouts.forEach((function(t) {
                                        var e = i(t.name, t.title, "layoutsRadios", z.contains("dark-style"));
                                        F.appendChild(e)
                                    })), F.querySelector('input[value="'.concat(this.settings.layoutCollapsed ? "collapsed" : "expanded", '"]')).setAttribute("checked", "checked");
                                    var I = function(e) {
                                        window.Helpers.setCollapsed("collapsed" === e.target.value, !0), t._setSetting("LayoutCollapsed", "collapsed" === e.target.value)
                                    };
                                    F.addEventListener("change", I), this._listeners.push([F, "change", I])
                                } else O.parentNode.removeChild(O);
                                var D = this.container.querySelector(".template-customizer-layoutNavbarOptions");
                                if (this._hasControls("layoutNavbarOptions")) {
                                    setTimeout((function() {
                                        k.includes("horizontal") && D.parentNode.removeChild(D)
                                    }), 100);
                                    var q = D.querySelector(".template-customizer-navbar-options");
                                    this.settings.availableNavbarOptions.forEach((function(t) {
                                        var e = i(t.name, t.title, "navbarOptionRadios", z.contains("dark-style"));
                                        q.appendChild(e)
                                    })), q.querySelector('input[value="'.concat(this.settings.layoutNavbarOptions, '"]')).setAttribute("checked", "checked");
                                    var P = function(e) {
                                        t._loading = !0, t._loadingState(!0, !0), t.setLayoutNavbarOption(e.target.value, !0, (function() {
                                            t._loading = !1, t._loadingState(!1, !0)
                                        }))
                                    };
                                    q.addEventListener("change", P), this._listeners.push([q, "change", P])
                                } else D.parentNode.removeChild(D)
                            } else b.parentNode.removeChild(b);
                            setTimeout((function() {
                                var e = t.container.querySelector(".template-customizer-layout");
                                document.querySelector(".menu-vertical") ? t._hasControls("rtl contentLayout layoutCollapsed layoutNavbarOptions", !0) || e && e.parentNode.removeChild(e) : document.querySelector(".menu-horizontal") && (t._hasControls("rtl contentLayout headerType", !0) || e && e.parentNode.removeChild(e))
                            }), 100), this.setLang(this.settings.lang, !1, !0), e === document ? e.body ? e.body.appendChild(this.container) : window.addEventListener("DOMContentLoaded", (function() {
                                return e.body.appendChild(t.container)
                            })) : e.appendChild(this.container)
                        }
                    }, {
                        key: "_initDirection",
                        value: function() {
                            this._hasControls("rtl") && document.documentElement.setAttribute("dir", this.settings.rtl ? "rtl" : "ltr")
                        }
                    }, {
                        key: "_initStyle",
                        value: function() {
                            if (this._hasControls("style")) {
                                var t = this.settings.style;
                                this._insertStylesheet("template-customizer-core-css", this.pathResolver(this.settings.cssPath + this.settings.cssFilenamePattern.replace("%name%", "core".concat("light" !== t ? "-".concat(t) : "")))), ("light" === t ? ["dark-style"] : ["light-style"]).forEach((function(t) {
                                    document.documentElement.classList.remove(t)
                                })), document.documentElement.classList.add("".concat(t, "-style"))
                            }
                        }
                    }, {
                        key: "_initTheme",
                        value: function() {
                            if (this._hasControls("themes")) this._insertStylesheet("template-customizer-theme-css", this.pathResolver(this.settings.themesPath + this.settings.cssFilenamePattern.replace("%name%", this.settings.theme.name + ("light" !== this.settings.style ? "-".concat(this.settings.style) : ""))));
                            else {
                                var t = this._getSetting("Theme");
                                this._insertStylesheet("template-customizer-theme-css", this.pathResolver(this.settings.themesPath + this.settings.cssFilenamePattern.replace("%name%", t || "theme-default" + ("light" !== this.settings.style ? "-".concat(this.settings.style) : ""))))
                            }
                        }
                    }, {
                        key: "_insertStylesheet",
                        value: function(t, e) {
                            var i = document.querySelector(".".concat(t));
                            if ("number" == typeof document.documentMode && document.documentMode < 11) {
                                if (!i) return;
                                if (e === i.getAttribute("href")) return;
                                var o = document.createElement("link");
                                o.setAttribute("rel", "stylesheet"), o.setAttribute("type", "text/css"), o.className = t, o.setAttribute("href", e), i.parentNode.insertBefore(o, i.nextSibling)
                            } else document.write('<link rel="stylesheet" type="text/css" href="'.concat(e, '" class="').concat(t, '">'));
                            i.parentNode.removeChild(i)
                        }
                    }, {
                        key: "_loadStylesheets",
                        value: function(t, e) {
                            var i = Object.keys(t),
                                o = i.length,
                                s = 0;

                            function a(t, e, i) {
                                var o = document.createElement("link");
                                o.setAttribute("href", t), o.setAttribute("rel", "stylesheet"), o.setAttribute("type", "text/css"), o.className = e.className;
                                var s, a = "sheet" in o ? "sheet" : "styleSheet",
                                    n = "sheet" in o ? "cssRules" : "rules",
                                    r = setTimeout((function() {
                                        clearInterval(s), clearTimeout(r), e.parentNode.removeChild(o), i(!1, t)
                                    }), 15e3);
                                s = setInterval((function() {
                                    try {
                                        o[a] && o[a][n].length && (clearInterval(s), clearTimeout(r), e.parentNode.removeChild(e), i(!0))
                                    } catch (t) {}
                                }), 10), e.parentNode.insertBefore(o, e.nextSibling)
                            }
                            for (var n = 0; n < i.length; n++) a(i[n], t[i[n]], void((s += 1) >= o && e()))
                        }
                    }, {
                        key: "_loadingState",
                        value: function(t, e) {
                            this.container.classList[t ? "add" : "remove"]("template-customizer-loading".concat(e ? "-theme" : ""))
                        }
                    }, {
                        key: "_getElementFromString",
                        value: function(t) {
                            var e = document.createElement("div");
                            return e.innerHTML = t, e.firstChild
                        }
                    }, {
                        key: "_getSetting",
                        value: function(t) {
                            var e = null,
                                i = this._getLayoutName();
                            try {
                                e = localStorage.getItem("templateCustomizer-".concat(i, "--").concat(t))
                            } catch (t) {}
                            return String(e || "")
                        }
                    }, {
                        key: "getColorPrimaryUse",
                        value: function() {
                            var t = null;
                            try {
                                t = localStorage.getItem("templateCustomizer-".concat(this._getLayoutName(), "--ColorPrimaryUse"))
                            } catch (t) {}
                            return t
                        }
                    }, {
                        key: "getSettingLocal",
                        value: function(t) {
                            var e = null,
                                i = this._getLayoutName();
                            try {
                                e = localStorage.getItem("templateCustomizer-".concat(i, "--").concat(t))
                            } catch (t) {}
                            return String(e || "")
                        }
                    }, {
                        key: "_showResetBtnNotification",
                        value: function() {
                            var t = this,
                                e = !(arguments.length > 0 && void 0 !== arguments[0]) || arguments[0];
                            setTimeout((function() {
                                var i = t.container.querySelector(".template-customizer-reset-btn .badge");
                                e ? i.classList.remove("d-none") : i.classList.add("d-none")
                            }), 200)
                        }
                    }, {
                        key: "_setSetting",
                        value: function(t, e) {
                            var i = this._getLayoutName();
                            try {
                                if (localStorage.setItem("templateCustomizer-".concat(i, "--").concat(t), String(e)), this._showResetBtnNotification(), "ColorPrimaryUse" == t) {
                                    var o = new Event("storageChanged", {
                                        bubbles: !0,
                                        cancelable: !0
                                    });
                                    document.dispatchEvent(o)
                                }
                            } catch (t) {}
                        }
                    }, {
                        key: "_getLayoutName",
                        value: function() {
                            return document.getElementsByTagName("HTML")[0].getAttribute("data-template")
                        }
                    }, {
                        key: "_removeListeners",
                        value: function() {
                            for (var t = 0, e = this._listeners.length; t < e; t++) this._listeners[t][0].removeEventListener(this._listeners[t][1], this._listeners[t][2])
                        }
                    }, {
                        key: "_cleanup",
                        value: function() {
                            this._removeListeners(), this._listeners = [], this._controls = {}, this._updateInterval && (clearInterval(this._updateInterval), this._updateInterval = null)
                        }
                    }, {
                        key: "_ssr",
                        get: function() {
                            return "undefined" == typeof window
                        }
                    }, {
                        key: "_hasControls",
                        value: function(t) {
                            var e = this,
                                i = arguments.length > 1 && void 0 !== arguments[1] && arguments[1];
                            return t.split(" ").reduce((function(t, o) {
                                return -1 !== e.settings.controls.indexOf(o) ? (i || !1 !== t) && (t = !0) : i && !0 === t || (t = !1), t
                            }), null)
                        }
                    }, {
                        key: "_getDefaultTheme",
                        value: function(t) {
                            var e;
                            if (!(e = "string" == typeof t ? this._getThemeByName(t, !1) : this.settings.availableThemes[t])) throw new Error('Theme ID "'.concat(t, '" not found!'));
                            return e
                        }
                    }, {
                        key: "_getThemeByName",
                        value: function(t) {
                            for (var e = arguments.length > 1 && void 0 !== arguments[1] && arguments[1], i = this.settings.availableThemes, o = 0, s = i.length; o < s; o++)
                                if (i[o].name === t) return i[o];
                            return e ? this.settings.defaultTheme : null
                        }
                    }], i && v(e.prototype, i), Object.defineProperty(e, "prototype", {
                        writable: !1
                    }), e;
                    var e, i
                }();
            F.COLORS = [{
                name: "color-1",
                title: "Theme 1"
            }, {
                name: "color-2",
                title: "Theme 2"
            }, {
                name: "color-3",
                title: "Theme 3"
            }, {
                name: "color-4",
                title: "Theme 4"
            }, {
                name: "color-5",
                title: "Theme 5"
            }], F.STYLES = [{
                name: "light",
                title: "Light"
            }, {
                name: "dark",
                title: "Dark"
            }, {
                name: "system",
                title: "System"
            }], F.THEMES = [{
                name: "theme-default",
                title: "Default"
            }, {
                name: "theme-bordered",
                title: "Bordered"
            }, {
                name: "theme-semi-dark",
                title: "Semi Dark"
            }], F.LAYOUTS = [{
                name: "expanded",
                title: "Expanded"
            }, {
                name: "collapsed",
                title: "Collapsed"
            }], F.NAVBAR_OPTIONS = [{
                name: "sticky",
                title: "Sticky"
            }, {
                name: "static",
                title: "Static"
            }, {
                name: "hidden",
                title: "Hidden"
            }], F.HEADER_TYPES = [{
                name: "fixed",
                title: "Fixed"
            }, {
                name: "static",
                title: "Static"
            }], F.CONTENT = [{
                name: "compact",
                title: "Compact"
            }, {
                name: "wide",
                title: "Wide"
            }], F.DIRECTIONS = [{
                name: "ltr",
                title: "Left to Right (En)"
            }, {
                name: "rtl",
                title: "Right to Left (Ar)"
            }], F.LANGUAGES = {
                vi: {
                    panel_header: "Modèle De Personnalisation",
                    panel_sub_header: "Personnalisez et prévisualisez en temps réel",
                    theming_header: "Thématisation",
                    color_label: "Color Customizer",
                    font_label: "Font family",
                    style_label: "Style (Mode)",
                    theme_label: "Thèmes",
                    layout_header: "Disposition",
                    layout_label: "Menu (Navigation)",
                    layout_header_label: "Types d'en-tête",
                    content_label: "Contenu",
                    layout_navbar_label: "Type de barre de navigation",
                    direction_label: "Direction"
                },
                en: {
                    panel_header: "Tùy chỉnh",
                    panel_sub_header: "",
                    theming_header: "Theming",
                    font_label: "Font family",
                    color_label: "Color Customizer",
                    style_label: "Style (Mode)",
                    theme_label: "Themes",
                    layout_header: "Layout",
                    layout_label: "Menu (Navigation)",
                    layout_header_label: "Header Types",
                    content_label: "Content",
                    layout_navbar_label: "Navbar Type",
                    direction_label: "Direction"
                },
                fr: {
                    panel_header: "Modèle De Personnalisation",
                    panel_sub_header: "Personnalisez et prévisualisez en temps réel",
                    theming_header: "Thématisation",
                    font_label: "Font family",
                    color_label: "Color Customizer",
                    style_label: "Style (Mode)",
                    theme_label: "Thèmes",
                    layout_header: "Disposition",
                    layout_label: "Menu (Navigation)",
                    layout_header_label: "Types d'en-tête",
                    content_label: "Contenu",
                    layout_navbar_label: "Type de barre de navigation",
                    direction_label: "Direction"
                },
                ar: {
                    panel_header: "أداة تخصيص القالب",
                    panel_sub_header: "تخصيص ومعاينة في الوقت الحقيقي",
                    theming_header: "السمات",
                    font_label: "Font family",
                    color_label: "Color Customizer",
                    style_label: "النمط (الوضع)",
                    theme_label: "المواضيع",
                    layout_header: "تَخطِيط",
                    layout_label: "القائمة (الملاحة)",
                    layout_header_label: "أنواع الرأس",
                    content_label: "محتوى",
                    layout_navbar_label: "نوع شريط التنقل",
                    direction_label: "اتجاه"
                },
                de: {
                    panel_header: "Vorlagen-Anpasser",
                    panel_sub_header: "Anpassen und Vorschau in Echtzeit",
                    theming_header: "Themen",
                    color_label: "Color Customizer",
                    style_label: "Stil (Modus)",
                    theme_label: "Themen",
                    layout_header: "Layout",
                    layout_label: "Menü (Navigation)",
                    layout_header_label: "Header-Typen",
                    content_label: "Inhalt",
                    layout_navbar_label: "Art der Navigationsleiste",
                    direction_label: "Richtung"
                }
            }
        }(), o
    }()
}));