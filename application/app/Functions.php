<?php
/**
 * Functions.php - This file declares functions in the global namespace. It's only intended for functions that are shims
 * for as-yet-unreleased PHP built-in functions, or for reasonable extensions of PHP built-ins. Anything else belongs
 * in a service class!
 */

/**
 * A version of array_intersect() that collapses an array of arrays into a single array containing only the intersections
 * of the sub-arrays. If the passed array contains only a single array, that single array's contents are returned. If the
 * passed array contains no arrays or only empty arrays, an empty array is returned.
 *
 * Examples:
 *   array_of_arrays_intersect([ [1, 2, 3], [2, 3, 4, 5], [1, 2, 3, 5] ]) => [2, 3]
 *   array_of_arrays_intersect([ [1, 2], [2, 3], [3, 4] ]) => []
 *   array_of_arrays_intersect([ [1, 2] ]) => [1, 2]
 *   array_of_arrays_intersect([ [] ]) => []
 *
 * The returned array is indexed, even if the passed arrays are associative. The order of the returned results is not
 * guaranteed.
 *
 * @param array $arrayOfArrays
 * @return array
 */
function array_of_arrays_intersect(array $arrayOfArrays) : array
{
    // Ensure arrays are indexed rather than associative
    $arrayOfArrays = array_map('array_values', $arrayOfArrays);

    if (count($arrayOfArrays) === 0) {
        return [];
    } elseif (count($arrayOfArrays) > 1) {
        return call_user_func_array('array_intersect',$arrayOfArrays);
    } else {
        return reset($arrayOfArrays);
    }
}

/**
 * Generates the union of all elements in all arrays contained within a parent array.
 *
 * The returned array is indexed, even if the passed arrays are associative. The order of the returned results is not
 * guaranteed.
 *
 * The returned array is unique; any duplicates in the passed arrays will be removed.
 *
 * @param array $arrayOfArrays
 * @return array
 */
function array_of_arrays_union(array $arrayOfArrays) : array
{
    // Ensure arrays are indexed rather than associative so that array_merge() won't replace items with identical keys
    $arrayOfArrays = array_map('array_values', $arrayOfArrays);

    return array_unique(call_user_func_array('array_merge', $arrayOfArrays));
}