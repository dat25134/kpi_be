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
} 