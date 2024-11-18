<?php

function get_category_paths() {
    global $DB;

    // Retrieve all categories
    $categories = $DB->get_records('lift_catalogue_categories', null, 'name ASC');
    $category_paths = [];  // To hold the category paths
    $categories_by_id = []; // To hold categories by ID for easy access

    // Organize categories by ID for quick access
    foreach ($categories as $category) {
        $categories_by_id[$category->id] = $category;
    }

    // Function to build category path recursively
    $build_path = function($category_id) use ($categories_by_id, &$build_path) {
        $category = isset($categories_by_id[$category_id]) ? $categories_by_id[$category_id] : null;

        // Base case: If category doesn't exist, return empty string
        if (!$category) {
            return '';
        }

        // If there's no parent, return the category name
        if (!$category->parent_id) {
            return $category->name;
        }

        // Recursive case: get the parent's path
        $parent_path = $build_path($category->parent_id);

        // Return the complete path
        return $parent_path ? $parent_path . ' > ' . $category->name : $category->name;
    };

    // Loop through each category to build its path
    foreach ($categories as $category) {
        $category_paths[] = (object) [
            'id' => $category->id,
            'path' => $build_path($category->id)
        ];
    }

    return $category_paths;
}

function build_tree($elements, $parent_id = 0) {
    $branch = [];
    
    foreach ($elements as $element) {
        // Assuming $element is an object, not an array.
        if (is_object($element) && $element->parent_id == $parent_id) {
            // Recursively build the tree
            $children = build_tree($elements, $element->id);
            if ($children) {
                $element->children = $children;  // Attach children to the current element
            }
            $branch[] = $element;  // Add the current element to the branch
        }
    }

    return $branch;
}

function render_dynamic_level($item) {
    // Main container for the hierarchy item
    $html = '<div class="hierarchy-item" style="border: none;">';
    
    // Header and toggler (with chevron icons, checkbox, and item name in the same row)
    $html .= '<div class="d-flex align-items-center courseindex-item courseindex-section-title ml-5">';

    // Only render the toggle button with chevron if the item has children
    if (isset($item->children) && !empty($item->children)) {
        // Toggle button with icons for collapse/expand
        $html .= '<a data-toggle="collapse" href="#collapse' . $item->id . '" role="button" aria-expanded="false" aria-controls="collapse' . $item->id . '" class="courseindex-chevron icons-collapse-expand collapsed">';

        // Collapsed chevron icon (right chevron)
        $html .= '<span class="collapsed-icon icon-no-margin p-1 mr-2" title="Expand">';
        $html .= '<span class="dir-rtl-hide" tabindex="-1">';
        $html .= '<i class="icon fa fa-chevron-right fa-fw" aria-hidden="true"></i>';
        $html .= '</span>';
        $html .= '</span>';

        // Expanded chevron icon (down chevron)
        $html .= '<span class="expanded-icon icon-no-margin p-1 mr-2" title="Collapse">';
        $html .= '<span class="dir-rtl-hide" tabindex="-1">';
        $html .= '<i class="icon fa fa-chevron-down fa-fw" aria-hidden="true"></i>';
        $html .= '</span>';
        $html .= '</span>';

        $html .= '</a>'; // End the link wrapping the toggle
    } else {
        // If no children, add a placeholder to align the checkbox and name properly
        $html .= '<span class="me-4 ml-3"></span>';
    }
    
    // Checkbox before the item name (placed outside the <a> tag to avoid triggering collapse)
    $editurl = new moodle_url('/local/lift_catalogue/manage_category.php', array('edit' => $item->id));
        $deleteurl = new moodle_url('/local/lift_catalogue/delete_category.php', array('id' => $item->id));
    
    // Item name with small font
    $html .= '<span class="small">' . $item->name . '</span>';
    $html .= html_writer::link($editurl, '<i class="fa fa-edit"></i>', array('class' => 'btn btn-primary btn-sm me-2', 'title' => get_string('edit')));
    $html .= html_writer::link($deleteurl, '<i class="fa fa-trash"></i>', array('class' => 'btn btn-danger btn-sm', 'title' => get_string('delete')));

    
    $html .= '</div>'; // End courseindex-section-title
    
    // Collapsible section for child elements (if any)
    if (isset($item->children) && !empty($item->children)) {
        $html .= '<div id="collapse' . $item->id . '" class="hierarchy-collapse collapse">';
        $html .= '<div class="hierarchy-body ms-3">'; // Indent child elements

        // Recursively render children if present
        foreach ($item->children as $child) {
            $html .= render_dynamic_level($child);
        }

        $html .= '</div>'; // End hierarchy-body
        $html .= '</div>'; // End collapsible section
    }

    $html .= '</div>'; // End hierarchy-item

    return $html;
}

function render_dynamic_level_with_link($item, $selected_category_id) {
    global $CFG;

    // Check if the current item is selected
    $is_selected = ($item->id == $selected_category_id);

    // Check if any child categories are selected
    $has_active_child = has_selected_child($item->id, $selected_category_id);

    // Determine if this item should expand
    $should_expand = $is_selected || $has_active_child;

    // Main container for the hierarchy item
    $html = '<div class="hierarchy-item" style="border: none;">';

    // Header and toggler (with chevron icons, checkbox, and item name in the same row)
    $html .= '<div class="d-flex align-items-center courseindex-item courseindex-section-title ml-5">';

    // Only render the toggle button with chevron if the item has children
    if (isset($item->children) && !empty($item->children)) {
        // Toggle button with icons for collapse/expand
        $html .= '<a data-toggle="collapse" href="#collapse' . $item->id . '" role="button" aria-expanded="' . ($should_expand ? 'true' : 'false') . '" aria-controls="collapse' . $item->id . '" class="courseindex-chevron icons-collapse-expand' . ($should_expand ? '' : ' collapsed') . '">';

        // Collapsed chevron icon (right chevron)
        $html .= '<span class="collapsed-icon icon-no-margin p-1 mr-2" title="Expand">';
        $html .= '<span class="dir-rtl-hide" tabindex="-1">';
        $html .= '<i class="icon fa fa-chevron-right fa-fw" aria-hidden="true"></i>';
        $html .= '</span>';
        $html .= '</span>';

        // Expanded chevron icon (down chevron)
        $html .= '<span class="expanded-icon icon-no-margin p-1 mr-2" title="Collapse">';
        $html .= '<span class="dir-rtl-hide" tabindex="-1">';
        $html .= '<i class="icon fa fa-chevron-down fa-fw" aria-hidden="true"></i>';
        $html .= '</span>';
        $html .= '</span>';

        $html .= '</a>'; // End the link wrapping the toggle
    } else {
        // If no children, add a placeholder to align the checkbox and name properly
        $html .= '<span class="me-4 ml-3"></span>';
    }

    // Link to the category with active class if this is the selected category
    $category_url = new moodle_url('/local/lift_catalogue/manage_category.php', ['category_id' => $item->id]);
    $active_class = ($item->id == $selected_category_id) ? ' bg-secondary' : '';

    $html .= '<span class="small' . $active_class . '">';
    $html .= html_writer::link($category_url, $item->name, ['class' => 'category-link']);
    $html .= '</span>';

    // Edit and delete links
    $editurl = new moodle_url('/local/lift_catalogue/manage_category.php', ['edit' => $item->id]);
    $deleteurl = new moodle_url('/local/lift_catalogue/manage_category.php', ['delete_category_id' => $item->id]);
    $html .= html_writer::link($editurl, '<i class="fa fa-edit"></i>', ['class' => 'btn btn-primary btn-sm me-2', 'title' => get_string('edit')]);
    $html .= html_writer::link($deleteurl, '<i class="fa fa-trash"></i>', ['class' => 'btn btn-danger btn-sm', 'title' => get_string('delete')]);

    $html .= '</div>'; // End courseindex-section-title

    // Collapsible section for child elements (if any)
    if (isset($item->children) && !empty($item->children)) {
        $html .= '<div id="collapse' . $item->id . '" class="hierarchy-collapse collapse' . ($should_expand ? ' show' : '') . '">';
        $html .= '<div class="hierarchy-body ms-3">'; // Indent child elements

        // Recursively render children if present
        foreach ($item->children as $child) {
            $html .= render_dynamic_level_with_link($child, $selected_category_id);
        }

        $html .= '</div>'; // End hierarchy-body
        $html .= '</div>'; // End collapsible section
    }

    $html .= '</div>'; // End hierarchy-item

    return $html;
}

function has_selected_child($category_id, $selected_category_id) {
    global $DB;

    // Query to get child categories of the current category
    $sql = "SELECT id FROM {lift_catalogue_categories} WHERE parent_id = :category_id";
    $params = ['category_id' => $category_id];

    // Fetch child categories
    $children = $DB->get_records_sql($sql, $params);

    if (empty($children)) {
        return false;
    }

    // Check if any of the child categories are selected
    foreach ($children as $child) {
        if ($child->id == $selected_category_id || has_selected_child($child->id, $selected_category_id)) {
            return true;
        }
    }

    return false;
}

function display_category_catalogue($selected_category_id = null) {
    global $DB, $CFG;
    $categories = $DB->get_records('lift_catalogue_categories', null, 'name ASC');

    echo html_writer::start_tag('div', ['class' => 'col-12 col-lg-6 d-flex flex-wrap px-3 mb-3 grid_column_start']);
    echo html_writer::start_tag('div', ['class' => 'category-listing card w-100']);

    if ($categories) {
        echo html_writer::tag('h3', get_string('category', 'local_lift_catalogue'), ['class' => 'card-header']);
        
        // Build a tree structure if necessary
        $tree_category = build_tree($categories);

        // Start rendering the categories list with links
        $html = '<div class="mb-3 mt-3 row fitem" id="hierarchy-expand"><div class="col-md-9 checkbox">';

        foreach ($tree_category as $item) {
            $html .= render_dynamic_level_with_link($item, $selected_category_id);
        }

        $html .= '</div></div>';
        echo $html;
    } else {
        echo html_writer::tag('p', get_string('nocategories', 'local_lift_catalogue'), ['class' => 'text-muted p-4']);
    }

    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

function display_course_catalogue($category_id) {
    global $DB, $OUTPUT, $PAGE;

    // Get the courses for the given category
    $category = $DB->get_record('lift_catalogue_categories', ['id' => $category_id ?? null]);
    $courses = $DB->get_records('lift_catalogue_courses', ['category_id' => $category_id ?? null], 'name ASC');

    // Container for the course catalogue
    echo html_writer::start_tag('div', ['class' => 'col-12 col-lg-6 d-flex flex-wrap px-3 mb-3 grid_column_start']);
    echo html_writer::start_tag('div', ['class' => 'category-listing card w-100']);
    echo html_writer::tag('h3', $category ? $category->name : get_string('course'), ['class' => 'card-header']);

    if ($courses) {
        // Start list of courses
        echo html_writer::start_tag('ul', ['class' => 'list-group p-4']);

        foreach ($courses as $course) {
            // Create action buttons (Edit, Hide, Delete)
            $editurl = new moodle_url('/local/lift_catalogue/manage_category.php', ['category_id' => $category_id, 'editcourse' => $course->id]);
            $deleteurl = new moodle_url('/local/lift_catalogue/manage_category.php', ['category_id' => $category_id, 'deletedcourse' => $course->id]);

            // Each course as a list item with its action buttons
            echo html_writer::start_tag('li', ['class' => 'list-group-item d-flex justify-content-between align-items-center', 'style' => 'border: none; padding: 8px 0;']);
            echo html_writer::tag('span', format_string($course->name));

            // Buttons for Edit, Hide, Delete
            echo html_writer::start_tag('div', ['class' => 'btn-group']);

            // Edit button (small icon)
            echo html_writer::link($editurl, html_writer::tag('i', '', ['class' => 'fa fa-pencil fa-sm', 'aria-hidden' => 'true']), ['class' => 'btn btn-primary btn-sm', 'title' => get_string('edit')]);

            // Delete button (small icon)
            echo html_writer::link($deleteurl, html_writer::tag('i', '', ['class' => 'fa fa-trash fa-sm', 'aria-hidden' => 'true']), ['class' => 'btn btn-danger btn-sm', 'title' => get_string('delete')]);

            echo html_writer::end_tag('div'); // End of button group

            echo html_writer::end_tag('li'); // End of course list item
        }

        echo html_writer::end_tag('ul'); // End of course list

    } else {
        // No courses found for the category
        echo html_writer::tag('p', get_string('nocourses', 'local_lift_catalogue'), ['class' => 'text-muted p-4']);
    }

    echo html_writer::end_tag('div'); // End of card
    echo html_writer::end_tag('div'); // End of column
}

