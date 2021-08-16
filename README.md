# property-plugin
Register Post type , taxonomy and meta field.
And show using shortcode.



function filter_by_priority_number( $clauses, $query_object ){
  
  global $leader_id,$wpdb,$post;
  
  if ( $query_object->query['custom'] == true ){
	
	$fields = &$clauses['fields'];
    if (! empty( $fields ) ) $fields .= ' '; // add a space only if we have to (for bonus marks!)
	
	$fields .= ",cast(mt2.meta_value as unsigned) as mv";

    $join = &$clauses['join'];
    if (! empty( $join ) ) $join .= ' '; // add a space only if we have to (for bonus marks!)
	
	$join .= "INNER JOIN ".$wpdb->prefix."postmeta AS mt2 ON ( ".$wpdb->prefix."posts.ID = mt2.post_id )";

    
    $where = &$clauses['where'];
    
    $where .= " AND ( mt2.meta_key = 'priority_number')"; // assuming $leader_id is always (int)

	$orderby = &$clauses['orderby'];
   
	$orderby = " mv ASC ";
	
	$limits = &$clauses['limits'];
   
    $limits = "";
    $groupby = &$clauses['groupby']; 
  }
  return $clauses;
}
add_filter( 'posts_clauses', 'filter_by_priority_number', 10, 2 );
