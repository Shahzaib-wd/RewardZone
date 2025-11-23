<?php
/**
 * Payment Class - Handles payment processing and transactions
 * 
 * @package RewardZone
 * @version 1.0
 */

class Payment {
    private $conn;
    private $transactions_table = "transactions";
    private $users_table = "users";

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Process JazzCash payment
     * @param int $user_id User ID
     * @param float $amount Amount
     * @return array Payment initiation data
     */
    public function initiateJazzCashPayment($user_id, $amount) {
        try {
            // Generate unique transaction ID
            $txn_id = 'RZ' . time() . $user_id;
            
            // Create pending transaction
            $this->createTransaction($user_id, 'deposit', $amount, 'pending', 'jazzcash', null, $txn_id);

            // Prepare JazzCash payment data
            $pp_Amount = $amount * 100; // Convert to paisa
            $pp_TxnDateTime = date('YmdHis');
            $pp_TxnExpiryDateTime = date('YmdHis', strtotime('+1 hour'));
            $pp_BillReference = $txn_id;
            
            // Create hash for integrity
            $sortedArray = array(
                'pp_Amount' => $pp_Amount,
                'pp_BillReference' => $pp_BillReference,
                'pp_Description' => 'RewardZone Pack Purchase',
                'pp_Language' => 'EN',
                'pp_MerchantID' => JAZZCASH_MERCHANT_ID,
                'pp_Password' => JAZZCASH_PASSWORD,
                'pp_ReturnURL' => JAZZCASH_RETURN_URL,
                'pp_TxnCurrency' => 'PKR',
                'pp_TxnDateTime' => $pp_TxnDateTime,
                'pp_TxnExpiryDateTime' => $pp_TxnExpiryDateTime,
                'pp_TxnRefNo' => $txn_id,
                'pp_Version' => '1.1',
                'ppmpf_1' => $user_id
            );

            $hash_string = JAZZCASH_INTEGRITY_SALT . '&';
            foreach ($sortedArray as $key => $value) {
                $hash_string .= $value . '&';
            }
            $hash_string = rtrim($hash_string, '&');
            $pp_SecureHash = hash_hmac('sha256', $hash_string, JAZZCASH_INTEGRITY_SALT);

            return [
                'success' => true,
                'payment_url' => 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/',
                'data' => array_merge($sortedArray, ['pp_SecureHash' => $pp_SecureHash])
            ];

        } catch (Exception $e) {
            error_log("JazzCash payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payment initiation failed'];
        }
    }

    /**
     * Process EasyPaisa payment
     * @param int $user_id User ID
     * @param float $amount Amount
     * @return array Payment initiation data
     */
    public function initiateEasyPaisaPayment($user_id, $amount) {
        try {
            // Generate unique transaction ID
            $txn_id = 'RZ' . time() . $user_id;
            
            // Create pending transaction
            $this->createTransaction($user_id, 'deposit', $amount, 'pending', 'easypaisa', null, $txn_id);

            // Prepare EasyPaisa payment data
            $postData = array(
                'storeId' => EASYPAISA_STORE_ID,
                'amount' => $amount,
                'postBackURL' => EASYPAISA_CALLBACK_URL,
                'orderRefNum' => $txn_id,
                'expiryDate' => date('YmdHis', strtotime('+1 hour')),
                'autoRedirect' => '1',
                'paymentMethod' => 'MA_PAYMENT_METHOD',
                'emailAddress' => '',
                'mobileNumber' => ''
            );

            // Create hash
            $hash_string = '';
            foreach ($postData as $key => $value) {
                $hash_string .= $value;
            }
            $hash_string .= EASYPAISA_SECRET_KEY;
            $hashRequest = hash('sha256', $hash_string);

            return [
                'success' => true,
                'payment_url' => 'https://easypaisa.com.pk/easypay/Index.jsf',
                'data' => array_merge($postData, ['hashRequest' => $hashRequest])
            ];

        } catch (Exception $e) {
            error_log("EasyPaisa payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payment initiation failed'];
        }
    }

    /**
     * Verify payment callback
     * @param array $data Callback data
     * @param string $method Payment method
     * @return array Verification result
     */
    public function verifyPayment($data, $method) {
        try {
            if ($method === 'jazzcash') {
                return $this->verifyJazzCashPayment($data);
            } elseif ($method === 'easypaisa') {
                return $this->verifyEasyPaisaPayment($data);
            }

            return ['success' => false, 'message' => 'Invalid payment method'];

        } catch (Exception $e) {
            error_log("Payment verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Verification failed'];
        }
    }

    /**
     * Verify JazzCash payment
     * @param array $data Payment data
     * @return array Verification result
     */
    private function verifyJazzCashPayment($data) {
        // Verify hash
        $response_hash = $data['pp_SecureHash'];
        unset($data['pp_SecureHash']);
        
        ksort($data);
        $hash_string = JAZZCASH_INTEGRITY_SALT . '&';
        foreach ($data as $key => $value) {
            $hash_string .= $value . '&';
        }
        $hash_string = rtrim($hash_string, '&');
        $calculated_hash = hash_hmac('sha256', $hash_string, JAZZCASH_INTEGRITY_SALT);

        if ($response_hash === $calculated_hash && $data['pp_ResponseCode'] === '000') {
            return [
                'success' => true,
                'transaction_id' => $data['pp_TxnRefNo'],
                'user_id' => $data['ppmpf_1'],
                'amount' => $data['pp_Amount'] / 100
            ];
        }

        return ['success' => false, 'message' => 'Payment verification failed'];
    }

    /**
     * Verify EasyPaisa payment
     * @param array $data Payment data
     * @return array Verification result
     */
    private function verifyEasyPaisaPayment($data) {
        // Verify hash and response code
        if (isset($data['responseCode']) && $data['responseCode'] === '0000') {
            return [
                'success' => true,
                'transaction_id' => $data['orderRefNum'],
                'amount' => $data['transactionAmount']
            ];
        }

        return ['success' => false, 'message' => 'Payment verification failed'];
    }

    /**
     * Process successful payment
     * @param int $user_id User ID
     * @param float $amount Amount
     * @param string $transaction_id Transaction ID
     * @return bool Success status
     */
    public function processSuccessfulPayment($user_id, $amount, $transaction_id) {
        try {
            $this->conn->beginTransaction();

            // Update transaction status
            $this->updateTransactionStatus($transaction_id, 'completed');

            // Activate user account
            $this->activateUserAccount($user_id);

            // Credit owner commission
            $this->creditOwnerCommission($user_id, OWNER_COMMISSION);

            // Process referral commission
            $this->processReferralCommission($user_id);

            // Create notification
            $this->createPaymentNotification($user_id, $amount);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Process payment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate user account
     * @param int $user_id User ID
     */
    private function activateUserAccount($user_id) {
        $query = "UPDATE " . $this->users_table . " SET is_active = 1 WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Credit owner commission
     * @param int $user_id User ID
     * @param float $amount Amount
     */
    private function creditOwnerCommission($user_id, $amount) {
        $query = "UPDATE " . $this->users_table . " 
                 SET balance = balance + :amount,
                     total_earned = total_earned + :amount
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        // Create transaction record
        $this->createTransaction($user_id, 'commission', $amount, 'completed', null, 'Owner commission for pack purchase');
    }

    /**
     * Process referral commission
     * @param int $user_id User ID (referred user)
     */
    private function processReferralCommission($user_id) {
        // Get referrer
        $query = "SELECT referred_by FROM " . $this->users_table . " WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $referrer_id = $row['referred_by'];
            
            if ($referrer_id) {
                // Check if referrer is active
                $referrer_query = "SELECT is_active FROM " . $this->users_table . " WHERE id = :referrer_id";
                $referrer_stmt = $this->conn->prepare($referrer_query);
                $referrer_stmt->bindParam(":referrer_id", $referrer_id);
                $referrer_stmt->execute();
                $referrer = $referrer_stmt->fetch(PDO::FETCH_ASSOC);

                $commission = $referrer['is_active'] ? ACTIVE_INVITER_COMMISSION : INACTIVE_INVITER_COMMISSION;

                // Credit commission
                $update_query = "UPDATE " . $this->users_table . " 
                               SET balance = balance + :commission,
                                   total_earned = total_earned + :commission,
                                   total_referrals = total_referrals + 1
                               WHERE id = :referrer_id";
                
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(":commission", $commission);
                $update_stmt->bindParam(":referrer_id", $referrer_id);
                $update_stmt->execute();

                // Create transaction
                $desc = "Referral commission for inviting user ID: $user_id";
                $this->createTransaction($referrer_id, 'referral', $commission, 'completed', null, $desc);

                // Create notification
                $notif_query = "INSERT INTO notifications (user_id, title, message, type)
                              VALUES (:user_id, 'Referral Bonus!', :message, 'success')";
                $notif_stmt = $this->conn->prepare($notif_query);
                $message = "You earned PKR $commission from your referral!";
                $notif_stmt->bindParam(":user_id", $referrer_id);
                $notif_stmt->bindParam(":message", $message);
                $notif_stmt->execute();

                // Update referrals table
                $ref_query = "INSERT INTO referrals (referrer_id, referred_id, commission_paid, referral_activated)
                            VALUES (:referrer_id, :referred_id, :commission, 1)";
                $ref_stmt = $this->conn->prepare($ref_query);
                $ref_stmt->bindParam(":referrer_id", $referrer_id);
                $ref_stmt->bindParam(":referred_id", $user_id);
                $ref_stmt->bindParam(":commission", $commission);
                $ref_stmt->execute();
            }
        }
    }

    /**
     * Create transaction record
     * @param int $user_id User ID
     * @param string $type Transaction type
     * @param float $amount Amount
     * @param string $status Status
     * @param string $method Payment method
     * @param string $description Description
     * @param string $transaction_id Transaction ID
     */
    private function createTransaction($user_id, $type, $amount, $status, $method = null, $description = null, $transaction_id = null) {
        $query = "INSERT INTO " . $this->transactions_table . "
                 (user_id, type, amount, status, payment_method, description, transaction_id)
                 VALUES (:user_id, :type, :amount, :status, :method, :description, :transaction_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":method", $method);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->execute();
    }

    /**
     * Update transaction status
     * @param string $transaction_id Transaction ID
     * @param string $status New status
     */
    private function updateTransactionStatus($transaction_id, $status) {
        $query = "UPDATE " . $this->transactions_table . " 
                 SET status = :status, processed_at = NOW() 
                 WHERE transaction_id = :transaction_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->execute();
    }

    /**
     * Create payment notification
     * @param int $user_id User ID
     * @param float $amount Amount
     */
    private function createPaymentNotification($user_id, $amount) {
        $query = "INSERT INTO notifications (user_id, title, message, type)
                 VALUES (:user_id, 'Payment Successful!', :message, 'success')";

        $stmt = $this->conn->prepare($query);
        $message = "Your payment of PKR $amount has been processed successfully. Your account is now active!";
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":message", $message);
        $stmt->execute();
    }
}
?>
