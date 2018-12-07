<?php
namespace BillingSystem\Controller;


class Index
{
	/**
     * install action
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - application_version
     * - auth_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function installAction($arguments)
    {
        $model = new \BillingSystem\Model\BillingSystem();
        $model->installShop($arguments);
    }

    /**
     * client paid for the app
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function billingInstallAction($arguments)
    {
        $model = new \BillingSystem\Model\BillingSystem();
        $model->billingInstall($arguments);
    }

	/**
     * upgrade action
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - application_version
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function upgradeAction($arguments)
    {
        $model = new \BillingSystem\Model\BillingSystem();
        $model->upgrade($arguments);
    }

    /**
     * app is being uninstalled
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function uninstallAction($arguments)
    {
        $model = new \BillingSystem\Model\BillingSystem();
        $model->uninstall($arguments);
    }

    /**
     * client paid for a subscription
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - subscription_end_time
     * - hash
     * - timestamp
     *
     * @param $arguments
     * @throws \Exception
     */
    public function billingSubscriptionAction($arguments)
    {
        $model = new \BillingSystem\Model\BillingSystem();
        $model->billingSubscription($arguments);
    }

}