<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\Wallet;
use App\Repository\CurrencyRepository;
use App\Repository\WalletRepository;
use App\Services\Wallet\WalletService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WalletsController extends AbstractFOSRestController
{
    public static function getSubscribedServices()
    {
        return [
                CurrencyRepository::class => '?' . CurrencyRepository::class,
                WalletRepository::class => '?' . WalletRepository::class,
                WalletService::class => '?' . WalletService::class,
            ] + parent::getSubscribedServices();
    }

    /**
     * @Get("/api/wallets", name="app_wallet_list", methods={"GET"})
     *
     * @SWG\Parameter(
     *     name="X-AUTH-TOKEN",
     *     in="header",
     *     type="string",
     *     description="put your API key",
     *     required=true,
     *     @SWG\Schema(
     *          type="string",
     *          example="1234567890"
     *     )
     * )
     * @SWG\Tag(name="wallets")
     * @SWG\Response(
     *     response="200",
     *     description="list of available wallets",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Wallet::class, groups={"wallet:list"}))
     *     )
     * )
     *
     * @Rest\View(serializerGroups={"wallet:list"})
     */
    public function listAction()
    {
        return $this->getWalletRepository()->findWalletsByUser($this->getUser());
    }

    /**
     * @Get("/api/wallet/{address}", name="api_wallet_ballance", requirements={"address"="\w+"})
     *
     * @SWG\Tag(name="wallets")
     * @SWG\Parameter(
     *     name="X-AUTH-TOKEN",
     *     in="header",
     *     type="string",
     *     description="put your API key"
     * )
     * @SWG\Parameter(
     *     name="address",
     *     in="path",
     *     type="string",
     *     description="Wallet's address",
     *     required=true,
     *     @SWG\Schema(
     *         type="strinng",
     *         example="1F1tAaz5x1HUXrCNLbtMDqcw6o5GNn4xqX"
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Wallet Ballance",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Wallet::class, groups={"wallet:show"}))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_NO_CONTENT,
     *     description="Nothing to be showed"
     * )
     *
     * @Rest\View(serializerGroups={"wallet:show"})
     */
    public function watchBalanceAction(Request $request, string $address)
    {
        $wallet = $this->getWalletRepository()->findOneBy(['address' => $address]);

        if ($wallet instanceof Wallet && $wallet->getUsers()->contains($this->getUser())) {
            return $wallet;
        }
    }

    /**
     * @Post("/api/wallets", name="api_wallets_add_to_watch_list")
     *
     * @SWG\Tag(name="wallets")
     * @SWG\Post(
     *     @SWG\Parameter(
     *         name="X-AUTH-TOKEN",
     *         in="header",
     *         type="string",
     *         description="put your API key",
     *         required=true,
     *         @SWG\Schema(
     *              type="string",
     *              example="1234567890"
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="payload",
     *         in="body",
     *         description="currency code",
     *         type="string",
     *         required=true,
     *         @SWG\Schema(
     *              type="object",
     *              example={"currency"="BTC", "address"="1F1tAaz5x1HUXrCNLbtMDqcw6o5GNn4xqX"},
     *              @SWG\Property(property="currency", type="string", enum={"BTC", "LTC", "ETH"}),
     *              @SWG\Property(property="address", type="string", description="eg:1F1tAaz5x1HUXrCNLbtMDqcw6o5GNn4xqX")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_CREATED,
     *         description="Add wallet to watch list",
     *         examples= {"application/json":{"status" = "success"}},
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="status", type="string", description="success or failed"),
     *             @SWG\Property(property="error", type="string", description="Error message")
     *        )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_BAD_REQUEST,
     *         description="Invalid input",
     *         examples= {"application/json": {"status"="failed", "error"="address is required field"}},
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(property="status", type="string", description="success or failed"),
     *             @SWG\Property(property="error", type="string", description="Error message")
     *        )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NO_CONTENT,
     *         description="Nothing to be showed"
     *     )
     * )
     */
    public function addWalletAction(Request $request)
    {
        try {
            $currency = strtoupper($request->request->get('currency'));
            $currency = $this->get(CurrencyRepository::class)->find($currency);

            if (!$currency instanceof Currency) {
                $this->createNotFoundException('currency not supported, pls check available currencies');
            }

            if (!$address = $request->request->get('address')) {
                throw new BadRequestHttpException('address is required');
            }

            $this->getWalletService()->addWallet($this->getUser(), $currency, $address);
        } catch (\Throwable $e) {
            return $this->handleView(
                $this->view(['status' => 'failed', 'error' => $e->getMessage(), ], Response::HTTP_BAD_REQUEST)
            );
        }

        return $this->handleView($this->view(['status' => 'success'], Response::HTTP_CREATED));

//        $wallet = new Wallet();
//        $wallet->addUser($this->getUser());
//        $form = $this->createFormBuilder($wallet, ['csrf_protection' => false,])
//            ->add('currency', EntityType::class, ['class' => Currency::class])
//            ->add('address', TextType::class)
//            ->getForm();
//        $form->submit(json_encode($data, true));
//        $form->isValid($request);
    }

    /**
     * @return WalletService
     */
    private function getWalletService()
    {
        return $this->get(WalletService::class);
    }

    /**
     * @return WalletRepository
     */
    private function getWalletRepository()
    {
        return $this->get(WalletRepository::class);
    }
}
