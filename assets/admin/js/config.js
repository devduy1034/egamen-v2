"use strict";
let config = {
      colors: {
        primary: "#a8aaae",
        secondary: "#a8aaae",
        success: "#28c76f",
        info: "#00cfe8",
        warning: "#ff9f43",
        danger: "#ea5455",
        dark: "#4b4b4b",
        black: "#000",
        white: "#fff",
        cardColor: "#fff",
        bodyBg: "#f8f7fa",
        bodyColor: "#6f6b7d",
        headingColor: "#5d596c",
        textMuted: "#a5a3ae",
        borderColor: "#dbdade"
      },
      colors_label: {
        primary: "#7367f029",
        secondary: "#a8aaae29",
        success: "#28c76f29",
        info: "#00cfe829",
        warning: "#ff9f4329",
        danger: "#ea545529",
        dark: "#4b4b4b29"
      },
      colors_dark: {
        cardColor: "#2f3349",
        bodyBg: "#25293c",
        bodyColor: "#b6bee3",
        headingColor: "#cfd3ec",
        textMuted: "#7983bb",
        borderColor: "#434968"
      },
      enableMenuLocalStorage: !0
    },
    assetsPath = document.documentElement.getAttribute("data-assets-path"),
    templateName = document.documentElement.getAttribute("data-template"),
    rtlSupport = !0;
"undefined" != typeof TemplateCustomizer && (window.templateCustomizer = new TemplateCustomizer({
  cssPath: assetsPath + "vendor/css" + (rtlSupport ? "/rtl" : "") + "/",
  themesPath: assetsPath + "vendor/css" + (rtlSupport ? "/rtl" : "") + "/",
  displayCustomizer: !0,
  lang: localStorage.getItem("templateCustomizer-" + templateName + "--Lang") || "en",
  defaultColor:'color-3',
  colorPrimary:{
    'color-3': {
      'primary':'#DB5363',
      'secondary':'#366AF0',
    },
    'color-1': {
      'primary':'#00C3F9',
      'secondary':'#573BFF',
    },
    'color-2': {
      'primary':'#91969E',
      'secondary':'#FD8D00',
    },
    'color-4': {
      'primary':'#EA6A12',
      'secondary':'#6410F1',
    },
    'color-5': {
      'primary':'#E586B3',
      'secondary':'#25C799',
    }
  },
  availableColors: [
  {
    name: "color-3",
    title: "Theme 1"
  },
  {
    name: "color-1",
    title: "Theme 2"
  }, {
    name: "color-2",
    title: "Theme 3"
  }, {
    name: "color-4",
    title: "Theme 4"
  }, {
    name: "color-5",
    title: "Theme 5"
  }],
  controls: ["style", "headerType", "layoutCollapsed", "layoutNavbarOptions", "themes", "colors"]
}));