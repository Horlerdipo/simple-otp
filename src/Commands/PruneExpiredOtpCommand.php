<?php

namespace Horlerdipo\SimpleOtp\Commands;

use App\Enums\EventStatus;
use App\Enums\InvitationStatus;
use App\Events\Event\EventReminder;
use App\Models\Event;
use App\Models\Invitation;
use Horlerdipo\SimpleOtp\Models\Otp;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PruneExpiredOtpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple-otp:prune-expired-otp {hours=24}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old and expired OTPs';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $hours = intval($this->argument('hours'));
        $time = Carbon::now()->addHours($hours);

        $affectedRows = Otp::query()
            ->where('created_at', '<', $time)
            ->where('expired_at', '<=', Carbon::now())
            ->delete();

        $this->info("Successfully deleted $affectedRows old and expired OTPs from the last $hours hours");
    }
}
