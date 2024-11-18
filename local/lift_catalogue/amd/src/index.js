define(['jquery', 'core/ajax', 'core/templates'], function($, ajax, templates) {
    return {
        init: function() {
            const loadCourses = (page = 0, limit = 12) => {
                $('#loading-icon').show();
                $('#courses-container').hide();

                const category = $('#typecatalogue').val();
                const coursetype = $('#methodFilter').val();

                ajax.call([{
                    methodname: 'local_lift_catalogue_get_courses',
                    args: {
                        page: page,
                        limit: limit,
                        category: category,       // Pass the selected category
                        coursetype: coursetype
                    }
                }])[0].done(function(response) {
                    // Render the courses.
                    templates.render('local_lift_catalogue/courses', { coursesData: response.courses })
                        .done(function(html) {
                            $('#courses-container').html(html).show();
                            $('#loading-icon').hide();
                        });

                    // Render the pagination.
                    const pages = [];
                    for (let i = 0; i < response.totalpages; i++) {
                        pages.push({
                            index: i,
                            displayindex: i + 1,
                            active: i == response.currentpage
                        });
                    }
                    templates.render('local_lift_catalogue/paginate', {
                        pages: pages,
                        previous: response.currentpage > 0 ? response.currentpage - 1 : null,
                        next: response.currentpage < (response.totalpages - 1) ? response.currentpage + 1 : null
                    }).done(function(html) {
                        $('#pagination-container').html(html);
                    });
                }).fail(function(ex) {
                    console.error(ex);
                    $('#loading-icon').hide(); // Hide loading icon on error
                     $('#courses-container').show();
                });
            };

            // Initial load.
            loadCourses();

            // Event listener for pagination links.
            $(document).on('click', '#pagination-container a.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                loadCourses(page);
            });
            
            // Event listeners for filter changes (load new course list on change).
            $('#typecatalogue, #methodFilter').on('change', function() {
                loadCourses();  // Reload courses based on selected filters.
            });
        }
    };
});
