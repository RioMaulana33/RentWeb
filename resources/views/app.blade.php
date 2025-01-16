<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="description" content="Metronic Vue Demo" />
  <link rel="icon" href="/media/misc/mobilelog.png">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">
  {{-- <link rel="stylesheet" href="/splash-screen.css"> --}}
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blucarra</title>

  <style>
    /* Style to hide the spinner arrows */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    input[type="number"] {
      -moz-appearance: textfield;
    }
  </style>

  @vite('resources/js/main.ts')
</head>

<body class="page-loading">
  <!--begin::Theme mode setup on page load-->
  <script>
    let themeMode = "system";

    if (localStorage.getItem("kt_theme_mode_value")) {
      themeMode = localStorage.getItem("kt_theme_mode_value");
    }

    if (themeMode === "system") {
      themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    }

    document.documentElement.setAttribute("data-bs-theme", themeMode);
  </script>
  <!--end::Theme mode setup on page load-->

  <div id="app"></div>

  <!--begin::Loading markup-->
  {{-- <div id="splash-screen" class="splash-screen">
    <img src="/media/logos/default-dark.svg" class="dark-logo" alt="Metronic dark logo" />
    <img src="/media/logos/default.svg" class="light-logo" alt="Metronic light logo" />
    <span>Loading ...</span>
  </div> --}}
  <!--end::Loading markup-->

</body>

</html>
