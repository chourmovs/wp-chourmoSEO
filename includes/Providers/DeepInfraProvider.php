<?php
namespace BreznGEO\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepInfraProvider implements ProviderInterface {
	private const API_URL = 'https://api.deepinfra.com/v1/openai/chat/completions';

	public function getId(): string {
		return 'deepinfra';
	}

	public function getName(): string {
		return 'DeepInfra';
	}

	public function getModels(): array {
		return array(
			'deepseek-ai/DeepSeek-V3.1-Terminus' => 'DeepSeek V3.1 Terminus (' . __( 'Recommended', 'brezngeo' ) . ')',
			'deepseek-ai/DeepSeek-R1'            => 'DeepSeek R1 (' . __( 'Reasoning', 'brezngeo' ) . ')',
			'Qwen/Qwen3-32B'                     => 'Qwen3 32B',
			'meta-llama/Llama-3.3-70B-Instruct-Turbo' => 'Llama 3.3 70B Instruct Turbo',
		);
	}

	public function testConnection( string $api_key ): array {
		try {
			$this->generateText( 'Say "ok"', $api_key, 'Qwen/Qwen3-32B', 5 );
			return array(
				'success' => true,
				'message' => __( 'Connection successful', 'brezngeo' ),
			);
		} catch ( \RuntimeException $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	public function generateText( string $prompt, string $api_key, string $model, int $max_tokens = 300 ): string {
		$response = wp_remote_post(
			self::API_URL,
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => $model,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => $prompt,
							),
						),
						'max_tokens' => $max_tokens,
					)
				),
			)
		);

		return $this->parseResponse( $response );
	}

	private function parseResponse( $response ): string {
		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( esc_html( $response->get_error_message() ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 ) {
			$msg = $body['error']['message'] ?? "HTTP $code";
			throw new \RuntimeException( esc_html( $msg ) );
		}

		return trim( $body['choices'][0]['message']['content'] ?? '' );
	}
}
