function autocomplete(inp) {
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/
        var currentFocus;
        var array;
        /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function (e) {
        closeAllLists();
        currentFocus = -1;
        set_filter(inp);
    });

    inp.addEventListener("focus", function (e) {
            // console.log(array);
        if (array == undefined) {
            $.ajax({
                url: site.base_url + "menu_permissions/search_menu",
                method : "GET",
                dataType : "json",
                success : function (data) {
                    array = data;
                    set_filter(inp);
                }
            });
        }else {
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
        inp.parentNode.appendChild(a);
        
        // console.log();
        var arrModule = [];
        html = '';
        array.forEach(function (value,index){
            let keys = getAllIndexes(arrayColumn(array, 'parent_id'), value.parent_id)
            if (arrModule.indexOf(value.parent_id) == -1 && keys.length > 1){
                cntSearch = 0;
                subMenu = '';
                keys.forEach(function (valueMenu, indexMenu) {
                    if (array[valueMenu].name.toUpperCase().indexOf(val.toUpperCase()) > -1) {
                        let attributeModal = 'data-toggle="" data-target=""';
                        if (array[valueMenu].is_modal==1){
                            attributeModal = 'data-toggle="modal" data-target="#myModal"  data-backdrop="static"';
                        }
                        cntSearch ++;
                        if (array[valueMenu].parent_id != array[valueMenu].menu_id) {
                            subMenu += '<div id="searchMenuslist-search" class="childMenus" >' +
                                '<a class="submenu" href = "' + site.base_url + array[valueMenu].menu_url + '" ' + attributeModal+'>' +
                                '<span style="float:right;">' +
                                '<i class="fa fa-cog hide"></i>' +
                                '</span>' +
                                '' + array[valueMenu].name + '' +
                                '<input type="hidden" value="' + site.base_url + array[valueMenu].menu_url + '">' +
                                '</a>' +
                                '</div >';
                        }
                    }
                });
                if (cntSearch> 0) {
                    classHide = '';
                }else{
                    classHide = 'hide';
                }
                html += '<div id="searchMenuslist-search-"' + value.parent_id + ' class="parrentMenus ' + classHide+'">'+
                                '<a class="" hreff = "" >'+
                                    '<span style="float:right;">'+
                                        '<i class="fa fa-cog hide"></i>'+
                                    '</span>'+
                                    '<strong>' + value.module_code+'</strong>'+
                                    '<input type="hidden" value="javascript:void(0)">'+
                            '</a>'+
                    '</div >' + subMenu;
                
                // }
                // a.appendChild(html);
                
                // $('.childMenus').click(function(){
                //     console.log(this.getElementsByTagName("input"));
                //     window.location = this.getElementsByTagName("input")[0].value;
                //     closeAllLists();
                // })
                arrModule.push(value.parent_id);
            }
        });
        $('#' + inp.id + "autocomplete-list").append(html);

        // for (i = 0; i < array.length; i++) {
        //     b = document.createElement("DIV");
        //     b.setAttribute("id", inp.id + "list-search");
        //     if (array[i].menu_url == '' || array[i].menu_url == null) {
        //         url = 'javascript:void(0)';
        //     } else {
        //         url = "<?= base_url()?>" + array[i].menu_url;
        //     }
        //     if (array[i].menu_id == array[i].parent_id) {
        //         b.classList.add('parrentMenus');
        //         text = '<strong>' + array[i].name + '</strong>';
        //         hideIcon = 'hide';
        //     } else {
        //         b.classList.add('childMenus');
        //         text = array[i].name;
        //         hideIcon = '';
        //     }
        //     if (array[i].name.toUpperCase().indexOf(val.toUpperCase()) > -1) {

        //         b.innerHTML = "<a class='' hreff=''>" + "<span style='float:right;'>" +
        //             "<i class='" + array[i].icon + " " + hideIcon + "'></i>" + "</span>" + text + "<input type='hidden' value=" + url + ">"
        //             + "</a>";
        //         b.addEventListener("click", function (e) {
        //             window.location = this.getElementsByTagName("input")[0].value;
        //             closeAllLists();
        //         });
        //         a.appendChild(b);
        //     }
        // }
    }

    function getAllIndexes(arr, val) {
        var indexes = [], i = -1;
        while ((i = arr.indexOf(val, i + 1)) != -1) {
            indexes.push(i);
        }
        return indexes;
    }
    function arrayColumn(array, columnName) {
        return array.map(function (value, index) {
            return value[columnName];
        })
    }
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function (e) {
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
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
    }

    autocomplete(document.getElementById("searchMenus"));

$(document).on('click', '.childMenus', function (e) {
    $(this).children('.submenu')[0].click();
})