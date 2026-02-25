<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Domain\Authorization;

final class UserAbility
{
	public const EXPORT_CLEAN_PDF = 'resume.export.clean_pdf';
	public const UPLOAD_RESUME_PHOTO = 'resume.upload_photo';
	public const ACCESS_PRO_TEMPLATES = 'templates.access_pro';
	public const ACCESS_AI_FEATURES = 'features.ai';
	public const REMOVE_WATERMARK = 'resume.remove_watermark';
	public const RESUME_VERSION_HISTORY = 'resume.version_history';

	private function __construct()
	{
	}

	public static function all(): array
	{
		return [
			self::EXPORT_CLEAN_PDF,
			self::UPLOAD_RESUME_PHOTO,
			self::ACCESS_PRO_TEMPLATES,
			self::ACCESS_AI_FEATURES,
			self::REMOVE_WATERMARK,
			self::RESUME_VERSION_HISTORY,
		];
	}
}
