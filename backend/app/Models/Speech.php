<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speech extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'recipient',
        'content',
        'paper_size',
        'template_type',
        'header_image',
        'footer_image',
        'signature_image',
    ];

    protected $appends = [
        'header_image_url',
        'footer_image_url',
        'signature_image_url',
        'content_preview'
    ];

    public static function getTemplates()
    {
        return [
            'official' => [
                'name' => 'خطاب رسمي',
                'header' => 'بسم الله الرحمن الرحيم',
                'content_template' => '<p>السيد المحترم / {recipient}</p><p>تحية طيبة وبعد،</p><p>{content}</p><p>وتفضلوا بقبول فائق الاحترام والتقدير</p>',
                'footer' => 'مع أطيب التحيات'
            ],
            'invitation' => [
                'name' => 'دعوة',
                'header' => 'دعوة كريمة',
                'content_template' => '<p>يتشرف {sender} بدعوة حضرتكم لحضور {event}</p><p>المكان: {location}</p><p>التاريخ: {date}</p><p>الوقت: {time}</p>',
                'footer' => 'نتطلع لحضوركم الكريم'
            ],
            'thanks' => [
                'name' => 'شكر وتقدير',
                'header' => 'شكر وتقدير',
                'content_template' => '<p>السيد المحترم / {recipient}</p><p>نتقدم لسيادتكم بجزيل الشكر والامتنان على {reason}</p><p>{content}</p>',
                'footer' => 'مع خالص الشكر والتقدير'
            ]
        ];
    }

    public function getHeaderImageUrlAttribute()
    {
        return $this->header_image ? asset($this->header_image) : null;
    }

    public function getFooterImageUrlAttribute()
    {
        return $this->footer_image ? asset($this->footer_image) : null;
    }

    public function getSignatureImageUrlAttribute()
    {
        return $this->signature_image ? asset($this->signature_image) : null;
    }

    public function getContentPreviewAttribute()
    {
        return strip_tags(substr($this->content, 0, 100)) . '...';
    }
}