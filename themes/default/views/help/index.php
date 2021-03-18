<!DOCTYPE html>
<html>

<head>
    <title><?= lang('Helps') ?> - <?= $Settings->site_name ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- All --> 
    <meta charset="utf-8">
    <meta name="author" content="PT. Sinergi Informatika Semen Indonesia">
    <meta name="description" content="Boosting Your Business Performance - Forca Point Of Sales is an online inventory management application for cashier and manager store">
    <meta name="keywords" content="Forca, Forca POS, Forca Point Of Sales, Point Of Sales, Kasir, Cashier, Forca Kasir, Cashier Forca, Forca Cashier, POS, Business Performance, Boosting Your Business Performance, Boosting Your Business">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@ForcaPos" />
    <meta name="twitter:creator" content="@ForcaPos" />
    <meta property="og:url" content="<?= current_url() ?>" />
    <meta property="og:title" content="Forca POS - Boosting Your Business Performance" />
    <meta property="og:description" content="Boosting Your Business Performance - Forca Point Of Sales is an online inventory management application for cashier and manager store." />
    <meta property="og:image" content="<?php echo $assets ?>images/Logo.png" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:url"                content="<?= current_url() ?>" />
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="Forca POS - Boosting Your Business Performance" />
    <meta property="og:description"        content="Boosting Your Business Performance - Forca Point Of Sales is an online inventory management application for cashier and manager store." />
    <meta property="og:image"              content="<?php echo $assets ?>images/Logo.png" />
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>help/css/style.css">
    <link rel="stylesheet" type="text/css" href="<?= $assets ?>help/css/jquery-ui.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="<?= $assets ?>help/js/jquery-ui.js"></script>
    <script src="<?= $assets ?>help/js/jquery-3.3.1.min.js"></script>
</head>
<style>
    .nav_logo {
        text-indent: -9999px !important;
        display: inline-block;
        background: url(<?= $assets ?>help/logo-sm.png);
        width: 138px;
        height: 34px;
        background-size: cover;
    }

    .heading_help {
        width: 100%;
        height: 300px;
        padding-top: 6%;
        margin-bottom: 40px;
        /* background-image: url(<?= $assets ?>help/banner-bakground.png); */
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100%;
        max-width: 1440px;
        margin: 0 auto 40px;
    }

    @media only screen and (max-width: 500px) {
        .heading_help {
            padding-top: 55%;
        }

        .title_search_help {
            font-size: 24px;
        }
    }


    /* google */
    #dropdownmenu {
        vertical-align: middle;
        cursor: pointer;
    }

    .dropper {
        position: relative;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        width: 285px;
        padding: 28px;
        border: 1px solid #ccc;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        right: 0px;
        background: #fff;
    }

    .appitem {
        width: 120px;
        display: inline-block;
        box-sizing: border-box;
        /* padding-left: 15px; */
        padding-top: 15px;
        border-radius: 10px;
        margin-top: 10px;
        margin-left: 15px;
        border: 1px solid rgba(0, 0, 0, 0.2);
    }

    .appitem:hover {
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 7px;
    }

    .appitemi {
        width: 80px;
        height: 80px;
    }

    .appitem-Label {
        color: rgba(0, 0, 0, 0.87);
        font-size: 12px;
    }

    .dropper:hover .dropdown-content {
        display: block;
    }

    img.logo-kotak {
        max-width: 20px;
    }

    .nav_item_wrapper a {
        margin-right: 8px;
    }

    .nav_item_wrapper a {
        color: #333;
    }

    .nav_item_wrapper a:hover {
        color: #007bff;
    }

    .nav_item {
        position: relative;
        float: left;
        padding: 10px 0;
        line-height: 1.4;
        padding-top: 13px;
    }

    img.logo-mbak {
        max-width: 60px;
    }

    .nav_brand {
        float: left;
        padding: 0;
        list-style: none;
        text-align: center;
        padding-top: 12px;
    }
    img.logo-forca {
        max-width: 300px;
    }
</style>

<body>

    <header class="header">

        <div class="nav_brand">
            <span>Pusat Bantuan</span>
        </div>
        <ul class="nav_menu">

            <li class="nav_item ">
                <div class="nav_item_wrapper ">
                    <?php if (!$this->session->userdata('user_id')) { ?>
                        <a href="<?php echo site_url('login'); ?>" class="btn_login">
                            <span class="nav_menu-outline">Masuk</span>
                        </a>
                        <!-- <span class="nav_item_divider"></span> -->
                        <a href="<?php echo site_url('auth/sign_up'); ?>" class="btn_register">
                            <span class="nav_menu_outline">Daftar</span>
                        </a>
                    <?php } ?>
                    <i class="fas fa-bell bell"></i>
                    <!--dropdown-->
                    <!-- <span class="dropper" style="margin-left: 10px;"> -->
                    <!-- <div class=""></div> -->
                    <!-- <img class="logo-kotak" src="<?= $assets ?>images/helps/iconsquare.png"> -->
                    <!-- <div class="dropdown-content"> -->

                    <!-- </div> -->
                    <!-- </span> -->

                    <!--dropdown-content-->

                </div>
            </li>

        </ul>

    </header>


    <section class="content_help">
        <div class="heading_help">
            <div class="" style="text-align: center;">
                <img class="logo-forca" src="<?= $assets ?>images/login_logo.png" alt="Penjualan" />
            </div>
            <div id="bg_black" class="bg_overlay bg_overlay_black"></div>
            <div class="search_help">
                <div class="search_bar">
                    <span class="search_icon"></span>
                    <input class="search_input_help" autocomplete="off" id="searchMenus" type="text" placeholder="Ketik sesuatu">
                    <button class="search_button_help hide-mb">Cari</button>
                </div>
                <div class="search_result"></div>
            </div>
            <div class="search_result" style="display: none;">
                <div id="search-result"></div>
            </div>
            <?php if (count($menu_image) == 3) { ?>
                <div class="icon_search_help_3">
                <?php } else { ?>
                    <div class="icon_search_help">
                    <?php } ?>

                        <div class="container">
                            <div class="row">
                            <?php foreach ($menu_image as $key) { ?>
                                <div class="col">
                                    <center>
                                        <a href="<?= site_url('helps/article/') . $key->id ?>" style="color: #212529;">
                                            <img class="appitemi" src=" <?= $assets ?>images/helps/<?= $key->image ?>" alt="<?= $key->menus ?>" />
                                            <p class="appitem-Label"><?= $key->menus ?></p>
                                        </a>
                                    </center>
                                </div>
                            <?php } ?>
                            </div>
                        </div>

                    </div>
                </div>
    </section>


    <script>
        $(document).ready(function() {
            $("#searchMenus").focusin(function() {
                $("#bg_black").addClass("bg_overlay_show");
            });
            $("#searchMenus").focusout(function() {
                $("#bg_black").removeClass("bg_overlay_show");
            });
        });
    </script>

    <script>
        function autocomplete(inp) {
            /*the autocomplete function takes two arguments,
            the text field element and an array of possible autocompleted values:*/
            var currentFocus;
            var array;
            /*execute a function when someone writes in the text field:*/
            inp.addEventListener("input", function(e) {
                closeAllLists();
                currentFocus = -1;
                set_filter(inp);
            });

            inp.addEventListener("focus", function(e) {
                // console.log(array);
                if (array == undefined) {
                    $.ajax({
                        url: "<?php echo site_url('helps/search_menu'); ?>",
                        method: "GET",
                        dataType: "json",
                        success: function(data) {
                            array = data;
                            set_filter(inp);
                        }
                    });
                } else {
                    set_filter(inp);
                }
            });

            function set_filter(inp) {
                var a, b, i, val = inp.value;
                // console.log(val);
                /*close any already open lists of autocompleted values*/
                closeAllLists();
                // if (!val) { return false;}
                currentFocus = -1;
                /*create a DIV element that will contain the items (values):*/
                a = document.createElement("DIV");
                a.setAttribute("id", inp.id + "autocomplete-list");
                a.setAttribute("class", "autocomplete-items");



                /*append the DIV element as a child of the autocomplete container:*/
                inp.parentNode.parentNode.appendChild(a);



                // console.log();
                var arrModule = [];
                html = '';
                array.forEach(function(value, index) {
                    let keys = getAllIndexes(arrayColumn(array, 'is_active'), value.is_active)
                    if (arrModule.indexOf(value.is_active) == -1 && keys.length > 1) {
                        cntSearch = 0;
                        subMenu = '';
                        keys.forEach(function(valueMenu, indexMenu) {
                            if (array[valueMenu].title.toUpperCase().indexOf(val.toUpperCase()) > -1) {

                                cntSearch++;
                                subMenu +=
                                    '<div id="searchMenuslist-search" class="childMenus" >' +
                                    '<a class="submenu" href = "' + "<?php echo site_url('helps/article/'); ?>" + array[valueMenu].id + '" ' + '>' +
                                    '<span style="float:right;">' +
                                    '<i class="fa fa-cog hide"></i>' +
                                    '</span>' +
                                    '' + array[valueMenu].menu + '' +
                                    '<input type="hidden" value="' + "<?php echo site_url('helps/article/'); ?>" + array[valueMenu].id + '">' +
                                    '</a>' +
                                    '</div >';

                            }
                        });
                        if (cntSearch > 0) {
                            classHide = '';
                        } else {
                            classHide = 'hide';
                        }
                        html += subMenu;


                        arrModule.push(value.parent_id);
                    }
                });
                $('#' + inp.id + "autocomplete-list").append(html);

            }

            function getAllIndexes(arr, val) {
                var indexes = [],
                    i = -1;
                while ((i = arr.indexOf(val, i + 1)) != -1) {
                    indexes.push(i);
                }
                return indexes;
            }

            function arrayColumn(array, columnName) {
                return array.map(function(value, index) {
                    return value[columnName];
                })
            }
            /*execute a function presses a key on the keyboard:*/
            inp.addEventListener("keydown", function(e) {
                var x = document.getElementById(this.id + "autocomplete-list");
                if (x) x = x.getElementsByTagName("div");
                if (e.keyCode == 40) {
                    /*If the arrow DOWN key is pressed,
                    increase the currentFocus variable:*/
                    currentFocus++;
                    /*and and make the current item more visible:*/
                    addActive(x);
                } else if (e.keyCode == 38) { //up
                    /*If the arrow UP key is pressed,
                    decrease the currentFocus variable:*/
                    currentFocus--;
                    /*and and make the current item more visible:*/
                    addActive(x);
                } else if (e.keyCode == 13) {
                    /*If the ENTER key is pressed, prevent the form from being submitted,*/
                    e.preventDefault();
                    if (currentFocus > -1) {
                        /*and simulate a click on the "active" item:*/
                        if (x) x[currentFocus].click();
                    }
                }
            });

            function addActive(x) {
                /*a function to classify an item as "active":*/
                if (!x) return false;
                /*start by removing the "active" class on all items:*/
                removeActive(x);
                if (currentFocus >= x.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (x.length - 1);
                /*add class "autocomplete-active":*/
                x[currentFocus].classList.add("autocomplete-active");
            }

            function removeActive(x) {
                /*a function to remove the "active" class from all autocomplete items:*/
                for (var i = 0; i < x.length; i++) {
                    x[i].classList.remove("autocomplete-active");
                }
            }

            function closeAllLists(elmnt) {
                /*close all autocomplete lists in the document,
                except the one passed as an argument:*/
                var x = document.getElementsByClassName("autocomplete-items");
                for (var i = 0; i < x.length; i++) {
                    if (elmnt != x[i] && elmnt != inp) {
                        x[i].parentNode.removeChild(x[i]);
                    }
                }
            }
            /*execute a function when someone clicks in the document:*/
            document.addEventListener("click", function(e) {
                closeAllLists(e.target);
            });
        }

        autocomplete(document.getElementById("searchMenus"));

        $(document).on('click', '.childMenus', function(e) {
            $(this).children('.submenu')[0].click();
        })
    </script>


</body>

</html>