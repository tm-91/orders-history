<?php

namespace DreamCommerce\ShopAppstoreLib\Resource;

use DreamCommerce\ShopAppstoreLib\Resource;

/**
 * Resource Parcel
 *
 * @package DreamCommerce\ShopAppstoreLib\Resource
 * @link https://developers.shoper.pl/developers/api/resources/parcels
 */
class Parcel extends Resource
{
    /**
     * It's not possibly to modify shipped parcel except shipping code
     */
    const HTTP_ERROR_PARCEL_CAN_NOT_MODIFY = 'parcel_cannot_modify';

    /**
     * Parcel has been already sent
     */
    const HTTP_ERROR_PARCEL_IS_ALREADY_SENT = 'parcel_already_sent';

    /**
     * address is used for billing purposes
     */
    const ADDRESS_TYPE_BILLING = 1;
    /**
     * address is used for delivery purposes
     */
    const ADDRESS_TYPE_DELIVERY = 2;

    protected $name = 'parcels';
}