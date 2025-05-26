import { Server } from '@/types/server';
import { CloudIcon, LoaderCircleIcon, MapPinIcon, MousePointerClickIcon, SlashIcon } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import ServerActions from '@/pages/servers/components/actions';
import { cn } from '@/lib/utils';
import { Site } from '@/types/site';
import { StatusRipple } from '@/components/status-ripple';
import { Badge } from '@/components/ui/badge';
import { useForm } from '@inertiajs/react';

export default function ServerHeader({ server, site }: { server: Server; site?: Site }) {
  const statusForm = useForm();

  const checkStatus = () => {
    if (['installing', 'installation_failed'].includes(server.status)) {
      return;
    }

    statusForm.patch(route('servers.status', { server: server.id }));
  };

  return (
    <div className="flex items-center justify-between border-b px-4 py-2">
      <div className="space-y-2">
        <div className="flex items-center space-x-2 text-xs">
          <Tooltip>
            <TooltipTrigger asChild>
              <div>
                {statusForm.processing && <LoaderCircleIcon className="size-3 animate-spin" />}
                {!statusForm.processing && <StatusRipple className="cursor-pointer" onClick={checkStatus} variant={server.status_color} />}
              </div>
            </TooltipTrigger>
            <TooltipContent side="left">
              <span>{server.status}</span>
            </TooltipContent>
          </Tooltip>
          <Tooltip>
            <TooltipTrigger asChild>
              <div className="flex items-center space-x-2">
                <div className="hidden lg:inline-flex">{server.name}</div>
              </div>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              <span className="lg:hidden">{server.name}</span>
              <span className="hidden lg:inline-flex">Server Name</span>
            </TooltipContent>
          </Tooltip>
          <SlashIcon className="size-3" />
          <Tooltip>
            <TooltipTrigger asChild>
              <div className="flex items-center space-x-1">
                <CloudIcon className="size-4" />
                <div className="hidden lg:inline-flex">{server.provider}</div>
              </div>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              <div>
                <span className="lg:hidden">{server.provider}</span>
                <span className="hidden lg:inline-flex">Server Provider</span>
              </div>
            </TooltipContent>
          </Tooltip>
          <SlashIcon className="size-3" />
          <Tooltip>
            <TooltipTrigger asChild>
              <div className="flex items-center space-x-1">
                <MapPinIcon className="size-4" />
                <div className="hidden lg:inline-flex">{server.ip}</div>
              </div>
            </TooltipTrigger>
            <TooltipContent side="bottom">
              <span className="lg:hidden">{server.ip}</span>
              <span className="hidden lg:inline-flex">Server IP</span>
            </TooltipContent>
          </Tooltip>
          {['installing', 'installation_failed'].includes(server.status) && (
            <>
              <SlashIcon className="size-3" />
              <Tooltip>
                <TooltipTrigger asChild>
                  <div className="flex items-center space-x-1">
                    <LoaderCircleIcon className={cn('size-4', server.status === 'installing' ? 'animate-spin' : '')} />
                    <div>%{parseInt(server.progress || '0')}</div>
                    {server.status === 'installation_failed' && (
                      <Badge className="ml-1" variant={server.status_color}>
                        {server.status}
                      </Badge>
                    )}
                  </div>
                </TooltipTrigger>
                <TooltipContent side="bottom">Status</TooltipContent>
              </Tooltip>
            </>
          )}
          {site && (
            <>
              <SlashIcon className="size-3" />
              <Tooltip>
                <TooltipTrigger asChild>
                  <a href={site.url} target="_blank" className="flex items-center space-x-1 truncate">
                    <MousePointerClickIcon className="size-4" />
                    <div className="hidden max-w-[150px] overflow-x-hidden overflow-ellipsis lg:block">{site.domain}</div>
                  </a>
                </TooltipTrigger>
                <TooltipContent side="bottom">
                  <span>{site.domain}</span>
                </TooltipContent>
              </Tooltip>
            </>
          )}
          {site && ['installing', 'installation_failed'].includes(site.status) && (
            <>
              <SlashIcon className="size-3" />
              <Tooltip>
                <TooltipTrigger asChild>
                  <div className="flex items-center space-x-1">
                    <LoaderCircleIcon className={cn('size-4', site.status === 'installing' ? 'animate-spin' : '')} />
                    <div>%{parseInt(site.progress.toString() || '0')}</div>
                    {site.status === 'installation_failed' && (
                      <Badge className="ml-1" variant={site.status_color}>
                        {site.status}
                      </Badge>
                    )}
                  </div>
                </TooltipTrigger>
                <TooltipContent side="bottom">Status</TooltipContent>
              </Tooltip>
            </>
          )}
        </div>
      </div>
      <div className="flex items-center space-x-1">
        <ServerActions server={server} />
      </div>
    </div>
  );
}
