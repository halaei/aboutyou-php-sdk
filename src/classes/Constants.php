<?php
namespace CollinsAPI;

/**
 * Contains often used properties related to the App development.
 *
 * @author Antevorte GmbH
 */
abstract class Constants
{
	const SDK_VERSION		=	0.1;
	
	const FACET_BRAND		=	0;
	const FACET_COLOR		=	1;
	const FACET_SIZE		=	2;
	const FACET_GENDERAGE	=	3;
	const FACET_CUPSIZE		=	4;
	const FACET_LENGTH		=	5;
	const FACET_DIMENSION3  =	6;
	
	const SORT_RELEVANCE	=	'relevance';
	const SORT_UPDATED		=	'updated_date';
	const SORT_CREATED		=	'created_date';
	const SORT_MOST_VIEWED	=	'most_viewed';
	const SORT_PRICE		=	'price';
	
	const TYPE_PRODUCTS		=	'products';
	const TYPE_CATEGORIES	=	'categories';
}