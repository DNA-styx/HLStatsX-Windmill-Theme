<?php

function display_menu_item($name, $link) {

  echo "    <li class=\"relative px-6 py-3\">\r\n";
  echo "        <a\r\n";
  echo "        class=\"inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200\"\r\n";
  echo "        href=\"" . $g_options['scripturl'] . $link . "\">\r\n";
  Echo "            <span class=\"ml-4\">- " . $name . "</span>\r\n";
  echo "        </a>\r\n";
  echo "    </li>\r\n";

}

function display_links($name, $link){

    echo "                      <li class=\"flex\">\r\n";
    echo "                          <a\r\n";
    echo "                              class=\"inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200\"\r\n";
    echo "                              href=\"" . $link . "\">\r\n";
    echo "                                  <span>" . $name ."</span>\r\n";
    echo "                          </a>\r\n";
    echo "                      </li>\r\n";
}

function display_page_title($title){

    echo "              <div\r\n";
    echo "                class=\"windmill-title-bar flex items-center justify-between p-4 mt-8 mb-8 text-l font-semibold rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple\"\r\n";
    echo "                >\r\n";
    echo "                  " . $title . "\r\n" ;
    echo "              </div>\r\n";

}

?>