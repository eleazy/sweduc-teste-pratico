function getUppermostParentWithClass(element, className) {
    var currentElement = element;
    while (currentElement) {
        if (currentElement.classList.contains(className)) {
            return currentElement;
        }
        currentElement = currentElement.parentElement;
    }
    return null;
}

document.addEventListener('DOMContentLoaded', function () {
    const headerUpper = document.querySelector('#header-upper');

    window.addEventListener('scroll', function () {
        let scrollTop = window.scrollY || document.documentElement.scrollTop;
        if (scrollTop > 100) {
            headerUpper.classList.add('hidden');
        } else {
            headerUpper.classList.remove('hidden');
        }
    });

    // Toggle menu display on mobile
    const menu = document.querySelector("#header-menu");
    const menuIcon = document.querySelector("#menu_button_inicio");
    var menuIconClicked = false;
    if (menuIcon) {
        menuIcon.addEventListener("click", function () {
            if (menu.style.display === "flex") {
                menu.style.display = "none";
            } else {
                menu.style.display = "flex";
            }
            menuIconClicked = true;
        });
    }

    // Change color of the current menu section
    window.onpopstate = function () {
      var currentUrl = navigation.currentEntry.url.split('#')[1];
      if (currentUrl == "/" || !currentUrl) return;

      var allMenuItems = document.querySelectorAll('#header-menu .mainOption');
      allMenuItems.forEach(function (item) {
          item.querySelector('a').classList.remove('current-menu-item');
      });

      var menuSubItem = document.querySelector(`#header-menu a[href="${currentUrl.replace('/', '#')}"]`);
      var menuItem = getUppermostParentWithClass(menuSubItem, 'mainOption');
      if (menuItem) menuItem.querySelector('a').classList.add('current-menu-item');

      // Esconde calendario de eventos ao trocar de pagina
      const calendarWrapper = d.querySelector('#calendar-wrapper');
      if (calendarWrapper) calendarWrapper.classList.add('calendarHide');
    };

    // Get all menu items with submenus
    var menuItems = document.querySelectorAll('#header-menu ul li');

    menuItems.forEach(function (menuItem) {
      // Check if the menu item has a submenu
      var submenu = menuItem.querySelector('ul');

      // Remove menu when clicking on a menu item
      menuItem.addEventListener('click', function (event) {
        if (menuIconClicked && !submenu) {
          menu.style.display = "none";
        }
      });

      if (submenu) {
        var timeoutId;
        var showDelayId;

        // Add SVG icon to menu items with submenus
        var linkElement = menuItem.querySelector('a');
        if (linkElement && linkElement.closest('ul').classList.contains('subOption')) { // If the menu item is a submenu
          linkElement.insertAdjacentHTML('beforeend', plusIcon);
        }

        // Add event listener for mouseenter
        menuItem.addEventListener('mouseenter', function () {
            clearTimeout(timeoutId);
            showDelayId = setTimeout(function () {
                submenu.classList.add('activeSubmenu');
                menuItem.closest('ul').classList.add('active');
                menuItem.classList.add('active');

            }, 150);
        });

        // Add event listener for mouseleave
        menuItem.addEventListener('mouseleave', function () {
            clearTimeout(showDelayId);
            timeoutId = setTimeout(function () {
              submenu.classList.remove('activeSubmenu');
                menuItem.closest('ul').classList.remove('active');
                menuItem.classList.remove('active');

            }, 300);
        });

        // Add event listener for mouseenter on submenu to cancel hiding
        submenu.addEventListener('mouseenter', function () {
            clearTimeout(timeoutId);
        });

      }
    });

    //Add icons to main menu items
    const labelToIcon = {
      "Alunos": studentIcon,
      "Marketing": marketingIcon,
      "Acadêmico": bookIcon,
      "Financeiro": moneyIcon,
      "Estoque": boxIcon,
      "Configurações": settingsIcon,
      "Sistema": settingsIcon2,
      "ContatosSW": supportIcon,
    };

    var mainMenuItems = document.querySelectorAll('#header-menu .mainOption');
    mainMenuItems.forEach(function (menuItem,i) {

        var linkElement = menuItem.querySelector('a');
        //if (i == 0) linkElement.classList.add('first-menu-item');// classe para alterar radius da borda
        //if (i == mainMenuItems.length - 1) linkElement.classList.add('last-menu-item');

        if (linkElement && labelToIcon[linkElement.textContent.replace(/\s/g, '')]) {
            linkElement.insertAdjacentHTML('beforeend', labelToIcon[linkElement.textContent.replace(/\s/g, '')]);
        }
    });
});
