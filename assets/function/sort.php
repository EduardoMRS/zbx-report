<?php
function sortArray($array, $sortColumn, $sortOrder)
{
    if (count($array) > 1) {
        usort($array, function ($a, $b) use ($sortColumn, $sortOrder) {
            if ($a[$sortColumn] == $b[$sortColumn]) {
                return 0;
            }

            if ($sortOrder === 'asc') {
                return ($a[$sortColumn] < $b[$sortColumn]) ? -1 : 1;
            } else {
                return ($a[$sortColumn] > $b[$sortColumn]) ? -1 : 1;
            }
        });
    }
    return $array;
}
