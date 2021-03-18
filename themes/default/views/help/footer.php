<!-- <footer class="footer">
    <div class="container">
        <p><?= FORCAPOS_COPYRIGHT ?> <a href="<?php echo site_url();?>"> @ Forca POS <?= FORCAPOS_VERSION ?></a></p>
    </div>
</footer> -->

<script>
    $(window).scroll(function(){
    var sticky = $('.article-sidebar'),
        scroll = $(window).scrollTop();
        console.log(scroll);

        // switch(scroll){
        //     case '>= 100' :
        //         ticky.css('top','8px');
        //     break;
        //     case '>= 110' :
        //         ticky.css('top','0px');
        //     break;
        //     default:
        //     sticky.css('top','107px');
        // }

        if (scroll >= 100 && scroll <= 190) {
            sticky.css('top','8px');
        }
        else if(scroll >= 200){
            sticky.css('top','0');
        } 
        else {
            sticky.css('top','107px');
        }
    });
</script>

<script>
    $(document).ready(function(){
        $("#searchMenus").focusin(function(){
            $("#bg_black").addClass("bg_overlay_show");
        });
        $("#searchMenus").focusout(function(){
            $("#bg_black").removeClass("bg_overlay_show");
        });
    });
</script>

<script>
   $(".article-menus-head").click(function(e){
      e.preventDefault();
      
      var $this = $(this).next('ul');
      var parent = $(this);
      console.log($this);
      if ($this.hasClass('buka')){
        $this.removeClass('article-menus_has-child--active buka');
        parent.removeClass('active');
      }else{
        $this.addClass('article-menus_has-child--active buka');
        parent.addClass('active');
      }
    });


</script>

<script>
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
                url:"<?php echo site_url('helps/search_menu');?>",
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
		inp.parentNode.parentNode.appendChild(a);
		
		
        
        // console.log();
        var arrModule = [];
        html = '';
        array.forEach(function (value,index){
            let keys = getAllIndexes(arrayColumn(array, 'is_active'), value.is_active)
            if (arrModule.indexOf(value.is_active) == -1 && keys.length > 1){
                cntSearch = 0;
                subMenu = '';
                keys.forEach(function (valueMenu, indexMenu) {
                    if (array[valueMenu].title.toUpperCase().indexOf(val.toUpperCase()) > -1) {
                        
                        cntSearch ++;
						subMenu += 
						'<div id="searchMenuslist-search" class="childMenus" >' +
							'<a class="submenu" href = "' + "<?php echo site_url('helps/article/');?>" + array[valueMenu].id + '" ' +'>' +
							'<span style="float:right;">' +
								'<i class="fa fa-cog hide"></i>' +
							'</span>' +
							'' + array[valueMenu].menu + '' +
							'<input type="hidden" value="' + "<?php echo site_url('helps/article/');?>" + array[valueMenu].id + '">' +
							'</a>' +
						'</div >';
                        
                    }
                });
                if (cntSearch> 0) {
                    classHide = '';
                }else{
                    classHide = 'hide';
                }
                html += subMenu;
                
              
                arrModule.push(value.parent_id);
            }
        });
        $('#' + inp.id + "autocomplete-list").append(html);

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
	
</script>


</body>
</html>