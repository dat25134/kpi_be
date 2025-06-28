<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Phòng Quản trị nền tảng số và VTTT',
                'code' => 'QTNT',
                'description' => 'Phụ trách quản trị hệ thống thông tin, phát triển nền tảng số và vận hành truyền thông.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Kỹ thuật',
                'code' => 'KT',
                'description' => 'Phụ trách kỹ thuật, bảo trì hệ thống và hỗ trợ kỹ thuật.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Nhân sự',
                'code' => 'NS',
                'description' => 'Quản lý nhân sự, tuyển dụng và đào tạo.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Tài chính - Kế toán',
                'code' => 'TCKT',
                'description' => 'Quản lý tài chính, kế toán và báo cáo tài chính.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Marketing',
                'code' => 'MKT',
                'description' => 'Phát triển chiến lược marketing, quảng cáo và thương hiệu.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Kinh doanh',
                'code' => 'KD',
                'description' => 'Phát triển kinh doanh, chăm sóc khách hàng và bán hàng.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Nghiên cứu và Phát triển',
                'code' => 'RND',
                'description' => 'Nghiên cứu công nghệ mới và phát triển sản phẩm.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Hành chính',
                'code' => 'HC',
                'description' => 'Quản lý hành chính, văn phòng và dịch vụ hỗ trợ.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Chăm sóc khách hàng',
                'code' => 'CSKH',
                'description' => 'Hỗ trợ và chăm sóc khách hàng, xử lý khiếu nại.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Đào tạo',
                'code' => 'DT',
                'description' => 'Đào tạo nhân viên, phát triển kỹ năng và kiến thức.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng An toàn thông tin',
                'code' => 'ATTT',
                'description' => 'Bảo mật thông tin, an ninh mạng và tuân thủ bảo mật.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Vận hành hệ thống',
                'code' => 'VHHT',
                'description' => 'Vận hành và giám sát hệ thống công nghệ thông tin.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Phát triển phần mềm',
                'code' => 'PTPS',
                'description' => 'Phát triển ứng dụng và phần mềm theo yêu cầu.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Kiểm soát chất lượng',
                'code' => 'KSCL',
                'description' => 'Kiểm soát chất lượng sản phẩm và dịch vụ.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Quản lý dự án',
                'code' => 'QLDA',
                'description' => 'Quản lý và điều phối các dự án công nghệ.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Phân tích dữ liệu',
                'code' => 'PHDL',
                'description' => 'Phân tích dữ liệu, báo cáo và hỗ trợ ra quyết định.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Thiết kế UI/UX',
                'code' => 'UIUX',
                'description' => 'Thiết kế giao diện người dùng và trải nghiệm người dùng.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Quản lý sản phẩm',
                'code' => 'QLSP',
                'description' => 'Quản lý vòng đời sản phẩm và chiến lược sản phẩm.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Hợp tác quốc tế',
                'code' => 'HTQT',
                'description' => 'Quản lý quan hệ đối tác và hợp tác quốc tế.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Pháp chế',
                'code' => 'PC',
                'description' => 'Tư vấn pháp lý, tuân thủ quy định và quản lý rủi ro.',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng Quản lý tài sản',
                'code' => 'QLTS',
                'description' => 'Quản lý tài sản, thiết bị và cơ sở vật chất.',
                'status' => 'active',
            ],
        ];

        foreach ($departments as $data) {
            Department::updateOrCreate(['code' => $data['code']], $data);
        }

        // Gán trưởng phòng cho phòng ban mẫu (nếu có user phù hợp)
        $manager = User::where('email', 'admin@gmail.com')->first();
        if ($manager) {
            $qtnt = Department::where('code', 'QTNT')->first();
            if ($qtnt) {
                $qtnt->manager_id = $manager->id;
                $qtnt->save();
            }
        }
    }
}