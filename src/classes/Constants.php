<?php
namespace CollinsAPI;

/**
 * Contains often used properties related to the App development.
 *
 * @author Antevorte GmbH
 */
abstract class Constants
{
	const SDK_VERSION					=	0.1;
	
	const FACET_BRAND					=	0;
	const FACET_COLOR					=	1;
	const FACET_SIZE					=	2;
	const FACET_GENDERAGE				=	3;
	const FACET_CUPSIZE					=	4;
	const FACET_LENGTH					=	5;
	const FACET_DIMENSION3				=	6;
	const FACET_SIZE_CODE				=	206;
	const FACET_SIZE_RUN				=	172;
	const FACET_CLOTHING_UNISEX_INT		=	173;
	const FACET_CLOTHING_UNISEX_INCH	=	174;
	const FACET_SHOES_UNISEX_EUR		=	194;
	const FACET_CLOTHING_WOMEN_DE		=	175;
	const FACET_CLOTHING_UNISEX_ONESIZE	=	204;
	const FACET_SHOES_UNISEX_ADIDAS_EUR	=	195;
	const FACET_CLOTHING_WOMEN_BELTS_CM	=	181;
	const FACET_CLOTHING_WOMEN_INCH		=	180;
	const FACET_CLOTHING_MEN_BELTS_CM	=	190;
	const FACET_CLOTHING_MEN_INCH		=	189;
	const FACET_CLOTHING_MEN_DE			=	187;
	
	const SORT_RELEVANCE	=	'relevance';
	const SORT_UPDATED		=	'updated_date';
	const SORT_CREATED		=	'created_date';
	const SORT_MOST_VIEWED	=	'most_viewed';
	const SORT_PRICE		=	'price';
	
	const TYPE_PRODUCTS		=	'products';
	const TYPE_CATEGORIES	=	'categories';
}