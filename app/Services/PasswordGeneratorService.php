<?php

namespace App\Services;

use Illuminate\Support\Str;

class PasswordGeneratorService
{
    /**
     * Tạo mật khẩu ngẫu nhiên an toàn
     * 
     * @param int $length Độ dài mật khẩu (mặc định 12)
     * @return string
     */
    public static function generateSecurePassword(int $length = 12): string
    {
        // Đảm bảo mật khẩu có ít nhất 1 chữ hoa, 1 chữ thường, 1 số, 1 ký tự đặc biệt
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        // Tạo mật khẩu với các ký tự bắt buộc
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)]; // 1 chữ hoa
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)]; // 1 chữ thường
        $password .= $numbers[random_int(0, strlen($numbers) - 1)]; // 1 số
        $password .= $symbols[random_int(0, strlen($symbols) - 1)]; // 1 ký tự đặc biệt
        
        // Thêm các ký tự ngẫu nhiên để đạt độ dài mong muốn
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Trộn ngẫu nhiên các ký tự trong mật khẩu
        return str_shuffle($password);
    }

    /**
     * Tạo mật khẩu dễ nhớ (dựa trên từ ngẫu nhiên)
     * 
     * @param int $wordCount Số từ (mặc định 3)
     * @return string
     */
    public static function generateMemorablePassword(int $wordCount = 3): string
    {
        $words = [
            'apple', 'banana', 'cherry', 'dragon', 'eagle', 'forest', 'garden', 'happy',
            'island', 'jungle', 'kitten', 'lemon', 'mountain', 'ocean', 'panda', 'queen',
            'river', 'sunset', 'tiger', 'umbrella', 'village', 'window', 'yellow', 'zebra'
        ];
        
        $password = '';
        for ($i = 0; $i < $wordCount; $i++) {
            $password .= ucfirst($words[array_rand($words)]);
        }
        
        // Thêm số ngẫu nhiên
        $password .= random_int(10, 99);
        
        return $password;
    }

    /**
     * Tạo mật khẩu theo yêu cầu cụ thể
     * 
     * @param array $options Các tùy chọn
     * @return string
     */
    public static function generateCustomPassword(array $options = []): string
    {
        $defaults = [
            'length' => 12,
            'uppercase' => true,
            'lowercase' => true,
            'numbers' => true,
            'symbols' => true,
            'exclude_similar' => true, // Loại trừ các ký tự dễ nhầm lẫn
        ];
        
        $options = array_merge($defaults, $options);
        
        $chars = '';
        if ($options['uppercase']) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($options['lowercase']) {
            $chars .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if ($options['numbers']) {
            $chars .= '0123456789';
        }
        if ($options['symbols']) {
            $chars .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }
        
        if ($options['exclude_similar']) {
            $chars = str_replace(['0', 'O', '1', 'l', 'I'], '', $chars);
        }
        
        $password = '';
        for ($i = 0; $i < $options['length']; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }

    /**
     * Sinh password theo quy tắc: Họ viết liền không dấu + 4 số cuối CCCD
     * @param string $name
     * @param string $cccd
     * @return string|null
     */
    public static function generatePasswordFromNameAndCCCD(string $name, string $cccd): ?string
    {
        if (empty($name) || empty($cccd) || strlen($cccd) < 4) {
            return null;
        }
        // Lấy tên (từ cuối cùng trong tên đầy đủ)
        $parts = preg_split('/\s+/', trim($name));
        $ten = end($parts);
        if (!$ten) return null;
        // Loại bỏ dấu tiếng Việt
        $ten_khong_dau = self::removeVietnameseAccents($ten);
        // Lấy 4 số cuối CCCD
        $cccd4 = substr($cccd, -4);
        return strtolower($ten_khong_dau . $cccd4);
    }

    /**
     * Loại bỏ dấu tiếng Việt khỏi chuỗi
     */
    public static function removeVietnameseAccents($str)
    {
        $accents = [
            'a'=>'áàảãạăắằẳẵặâấầẩẫậ',
            'A'=>'ÁÀẢÃẠĂẮẰẲẴẶÂẤẦẨẪẬ',
            'd'=>'đ', 'D'=>'Đ',
            'e'=>'éèẻẽẹêếềểễệ', 'E'=>'ÉÈẺẼẸÊẾỀỂỄỆ',
            'i'=>'íìỉĩị', 'I'=>'ÍÌỈĨỊ',
            'o'=>'óòỏõọôốồổỗộơớờởỡợ',
            'O'=>'ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢ',
            'u'=>'úùủũụưứừửữự', 'U'=>'ÚÙỦŨỤƯỨỪỬỮỰ',
            'y'=>'ýỳỷỹỵ', 'Y'=>'ÝỲỶỸỴ'
        ];
        foreach ($accents as $nonAccent => $accentGroup) {
            $str = preg_replace("/[$accentGroup]/u", $nonAccent, $str);
        }
        return $str;
    }
} 